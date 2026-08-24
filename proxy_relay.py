#!/usr/bin/env python3
"""
RenchiXmodz Proxy Relay Server
Acts as a TCP/TLS proxy relay for Free Fire game traffic.
"""
import asyncio
import ssl
import json
import logging
import os
import sys
import hashlib
import time
import aiohttp
from datetime import datetime

# Configuration
CONFIG = {
    "bind_host": "0.0.0.0",
    "bind_port": 443,
    "api_base_url": "https://renchixmodz.xyz/api.php",
    "api_key": "admin-secret-key-change-me",  # Match api.php
    "tls_cert": "/etc/letsencrypt/live/renchixmodz.xyz/fullchain.pem",
    "tls_key": "/etc/letsencrypt/live/renchixmodz.xyz/privkey.pem",
    "token_verify_interval": 300,  # Verify token every 5 minutes
    "max_connections": 1000,
    "buffer_size": 65536,
    "log_file": "/var/log/renchixmodz/proxy.log"
}

# Real Free Fire server endpoints (game servers)
FF_SERVERS = {
    "us_east": {"host": "ff-us-east.garena.com", "port": 10001},
    "us_west": {"host": "ff-us-west.garena.com", "port": 10001},
    "eu": {"host": "ff-eu.garena.com", "port": 10001},
    "sg": {"host": "ff-sg.garena.com", "port": 10001},
    "jp": {"host": "ff-jp.garena.com", "port": 10001},
    "br": {"host": "ff-br.garena.com", "port": 10001},
    "in": {"host": "ff-in.garena.com", "port": 10001},
}

# Setup logging
os.makedirs(os.path.dirname(CONFIG["log_file"]), exist_ok=True)
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.FileHandler(CONFIG["log_file"]),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger("proxy_relay")

class ProxyClient:
    """Manages a single proxied connection."""
    
    def __init__(self, reader, writer, session_id, target_host, target_port):
        self.reader = reader
        self.writer = writer
        self.session_id = session_id
        self.target_host = target_host
        self.target_port = target_port
        self.bytes_sent = 0
        self.bytes_received = 0
        self.start_time = time.time()
        self.remote_reader = None
        self.remote_writer = None
        
    async def connect_remote(self):
        """Connect to the actual game server."""
        try:
            self.remote_reader, self.remote_writer = await asyncio.wait_for(
                asyncio.open_connection(self.target_host, self.target_port),
                timeout=10
            )
            logger.info(f"[{self.session_id}] Connected to {self.target_host}:{self.target_port}")
            return True
        except Exception as e:
            logger.error(f"[{self.session_id}] Failed to connect to remote: {e}")
            return False
    
    async def relay_data(self, source_reader, dest_writer, direction, callback):
        """Relay data from source to destination."""
        try:
            while True:
                data = await source_reader.read(CONFIG["buffer_size"])
                if not data:
                    break
                dest_writer.write(data)
                await dest_writer.drain()
                callback(len(data))
                logger.debug(f"[{self.session_id}] {direction}: {len(data)} bytes")
        except Exception as e:
            logger.debug(f"[{self.session_id}] {direction} relay ended: {e}")
    
    def add_sent(self, bytes_count):
        self.bytes_sent += bytes_count
    
    def add_received(self, bytes_count):
        self.bytes_received += bytes_count
    
    async def start_relay(self):
        """Start bidirectional relay."""
        task1 = asyncio.create_task(
            self.relay_data(self.reader, self.remote_writer, "->", self.add_sent)
        )
        task2 = asyncio.create_task(
            self.relay_data(self.remote_reader, self.writer, "<-", self.add_received)
        )
        await asyncio.gather(task1, task2)
        
        elapsed = time.time() - self.start_time
        logger.info(
            f"[{self.session_id}] Session ended. "
            f"Sent: {self.bytes_sent/1024:.1f}KB, "
            f"Received: {self.bytes_received/1024:.1f}KB, "
            f"Duration: {elapsed:.1f}s"
        )
        return {
            "bytes_sent": self.bytes_sent,
            "bytes_received": self.bytes_received,
            "duration_seconds": elapsed
        }
    
    async def cleanup(self):
        """Close all connections."""
        for w in [self.writer, self.remote_writer]:
            if w and not w.is_closing():
                try:
                    w.close()
                    await w.wait_closed()
                except:
                    pass

class ProxyServer:
    """Main proxy server handling client connections and token verification."""
    
    def __init__(self):
        self.active_clients = {}
        self.verified_tokens = {}
    
    async def verify_token(self, token):
        """Verify a proxy token with the API."""
        try:
            url = f"{CONFIG['api_base_url']}?endpoint=verify&token={token}"
            async with aiohttp.ClientSession() as session:
                async with session.get(url, timeout=10) as resp:
                    if resp.status == 200:
                        data = await resp.json()
                        if data.get("valid"):
                            return data
            return None
        except Exception as e:
            logger.error(f"Token verification error: {e}")
            return None
    
    async def authenticate_client(self, reader, writer):
        """Handle initial client authentication."""
        try:
            data = await asyncio.wait_for(reader.read(1024), timeout=15)
            if not data:
                return None
            
            # Expect JSON: {"token": "...", "region": "..."}
            try:
                payload = json.loads(data.decode().strip())
                token = payload.get("token", "")
                region = payload.get("region", "")
            except (json.JSONDecodeError, UnicodeDecodeError):
                writer.write(b'{"error":"Invalid auth format"}\n')
                await writer.drain()
                writer.close()
                return None
            
            if not token or not region:
                writer.write(b'{"error":"Token and region required"}\n')
                await writer.drain()
                writer.close()
                return None
            
            if region not in FF_SERVERS:
                writer.write(f'{{"error":"Unknown region: {region}"}}\n'.encode())
                await writer.drain()
                writer.close()
                return None
            
            # Verify token
            user_data = await self.verify_token(token)
            if not user_data:
                writer.write(b'{"error":"Invalid or expired token"}\n')
                await writer.drain()
                writer.close()
                return None
            
            session_id = user_data.get("session_id", hashlib.md5(token.encode()).hexdigest()[:16])
            target = FF_SERVERS[region]
            
            writer.write(json.dumps({
                "status": "authenticated",
                "session_id": session_id,
                "server": target["host"],
                "port": target["port"],
                "user_id": user_data.get("user_id"),
                "ff_uid": user_data.get("free_fire_uid")
            }).encode() + b'\n')
            await writer.drain()
            
            logger.info(f"[{session_id}] Authenticated - User: {user_data.get('username')}, "
                       f"Region: {region}, FF UID: {user_data.get('free_fire_uid')}")
            
            return {
                "session_id": session_id,
                "target_host": target["host"],
                "target_port": target["port"],
                "reader": reader,
                "writer": writer,
                "token": token
            }
            
        except asyncio.TimeoutError:
            writer.close()
            return None
        except Exception as e:
            logger.error(f"Auth error: {e}")
            writer.close()
            return None
    
    async def handle_client(self, reader, writer):
        """Handle a new client connection."""
        client_ip = writer.get_extra_info('peername')[0]
        logger.info(f"New connection from {client_ip}")
        
        # Authenticate
        auth_result = await self.authenticate_client(reader, writer)
        if not auth_result:
            return
        
        session_id = auth_result["session_id"]
        
        try:
            # Create proxy client
            proxy = ProxyClient(
                reader, writer,
                session_id,
                auth_result["target_host"],
                auth_result["target_port"]
            )
            self.active_clients[session_id] = proxy
            
            # Connect to remote
            if not await proxy.connect_remote():
                writer.write(b'{"error":"Failed to connect to game server"}\n')
                await writer.drain()
                return
            
            # Relay traffic
            await proxy.start_relay()
            
        except Exception as e:
            logger.error(f"[{session_id}] Proxy error: {e}")
        finally:
            if session_id in self.active_clients:
                del self.active_clients[session_id]
            await proxy.cleanup()
    
    async def start(self):
        """Start the proxy server."""
        # Check TLS certificates
        use_tls = os.path.exists(CONFIG["tls_cert"]) and os.path.exists(CONFIG["tls_key"])
        
        if use_tls:
            ssl_context = ssl.create_default_context(ssl.Purpose.CLIENT_AUTH)
            ssl_context.load_cert_chain(CONFIG["tls_cert"], CONFIG["tls_key"])
            logger.info(f"TLS enabled - cert: {CONFIG['tls_cert']}")
        
        server = await asyncio.start_server(
            self.handle_client,
            CONFIG["bind_host"],
            CONFIG["bind_port"],
            ssl=ssl_context if use_tls else None,
            limit=CONFIG["buffer_size"]
        )
        
        addr = server.sockets[0].getsockname()
        logger.info(f"Proxy relay server running on {addr[0]}:{addr[1]} {'(TLS)' if use_tls else '(No TLS)'}")
        logger.info(f"Game servers mapped: {len(FF_SERVERS)} regions")
        
        async with server:
            await server.serve_forever()

if __name__ == "__main__":
    print("""
    ╔══════════════════════════════════════╗
    ║   RenchiXmodz Proxy Relay Server     ║
    ║   Free Fire Game Traffic Proxy       ║
    ╚══════════════════════════════════════╝
    """)
    
    try:
        asyncio.run(ProxyServer().start())
    except KeyboardInterrupt:
        logger.info("Server shutdown requested.")
    except Exception as e:
        logger.error(f"Fatal error: {e}")
        sys.exit(1)

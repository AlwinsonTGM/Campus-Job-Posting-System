# ??? System Tools & Cloudflare Tunnels

This folder contains helper scripts for running live network tunnels during presentations.

## Available Scripts

- **start-tunnel.bat**: Starts a public Cloudflare Tunnel exposing your local XAMPP web server (http://localhost:80) to a secure https://*.trycloudflare.com URL. Use this if panel members want to test the website live on their phones or personal devices.
- **stop-tunnel.bat**: Immediately stops background PHP and Cloudflare tunnel processes.
- **start-dual-tunnels.bat**: Advanced utility for running dual branches side-by-side for comparison testing.

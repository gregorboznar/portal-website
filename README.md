# Social Platform

A modern social networking platform featuring real-time chat, social feeds, events management, and member connections.

## 🚀 Features

### Core Social Features

- **Social Feed**: Create, share, and interact with posts including text, images, and polls
- **Real-time Chat**: Private messaging with live typing indicators and presence detection
- **Friendship System**: Send/accept friend requests, manage connections
- **Member Directory**: Browse and discover community members
- **Events Management**: Create, view, and manage community events
- **User Profiles**: Customizable profiles with cover images, social links, and badges
- **Real-time Presence**: See who's online across the platform

## 🔧 Technology Stack

### Backend

- **Laravel 12**: PHP framework with Inertia.js integration
- **Laravel Reverb**: WebSocket server for real-time features
- **MySQL**: Primary database
- **Redis**: Session storage and caching
- **Intervention Image**: Image processing

### Frontend

- **Vue.js 3**: JavaScript framework with Composition API
- **Inertia.js**: SPA-like experience without API complexity
- **TypeScript**: Type-safe development
- **Tailwind CSS**: Utility-first styling
- **Reka UI**: Vue component library

## 💬 Real-time Features

The platform includes sophisticated real-time capabilities powered by Laravel Reverb:

- **Instant Messaging**: Direct conversations with immediate delivery
- **Live Presence**: See who's online in real-time
- **Message Status**: Read receipts and delivery confirmation
- **Typing Indicators**: Live typing status in conversations
- **Push Notifications**: Real-time alerts for new messages and interactions

### Core Components

- **Social Feed System**: Post creation, interactions, and real-time updates
- **Chat System**: Private messaging with conversation management
- **Friendship Network**: Connection requests and management
- **Event Management**: Community event creation and participation
- **User Management**: Profiles, authentication, and permissions
- **Media Handling**: Image upload, processing, and optimization

## 🚀 Deployment

### Automatic Deployment with GitHub Actions

This project includes automatic deployment to production when code is pushed to the `main` branch.

#### Setup Instructions

1. **Add GitHub Secrets** (in your GitHub repository settings):
   - Go to your repository → Settings → Secrets and variables → Actions
   - Add the following secrets:
     - `SERVER_HOST`: Your server's IP address or domain
     - `SERVER_USERNAME`: SSH username (e.g., `jost`)
     - `SERVER_SSH_KEY`: Your server's private SSH key
     - `SERVER_PORT`: SSH port (usually `22`)

2. **Server Requirements**:
   - Docker and Docker Compose installed
   - SSH access configured
   - Git repository cloned at `~/web/portal.glab.si/public_html`

3. **How it works**:
   - When you push to `main` branch, GitHub Actions automatically:
     - Connects to your server via SSH
     - Pulls the latest code from GitHub
     - Runs the deployment script (`deploy.sh`)
     - Verifies the deployment was successful

#### Manual Deployment

If you need to deploy manually:

```bash
# On your server
cd ~/web/portal.glab.si/public_html
git pull origin main
./deploy.sh
```

#### Deployment Script

The `deploy.sh` script handles:
- Pulling latest code from Git
- Building Docker containers
- Running database migrations
- Setting up environment
- Starting all services
- Health checks

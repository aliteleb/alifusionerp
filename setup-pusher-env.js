#!/usr/bin/env node

/**
 * Pusher Environment Setup Script
 * 
 * This script helps set up the required environment variables for Pusher WebSocket connections.
 * Run this script to automatically add the Pusher configuration to your .env file.
 */

const fs = require('fs');
const path = require('path');

const envPath = path.join(process.cwd(), '.env');
const envExamplePath = path.join(process.cwd(), '.env.example');

// Pusher configuration from BROADCASTING_CONFIG.txt
const pusherConfig = `
# Broadcasting Configuration
BROADCAST_DRIVER=pusher

# Your Pusher Credentials
PUSHER_APP_ID=1791185
PUSHER_APP_KEY=2d563051278e834aeb65
PUSHER_APP_SECRET=932b11a90f735691cf14
PUSHER_APP_CLUSTER=eu

# Frontend Configuration (for Vite)
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Queue Configuration (required for real-time broadcasting)
QUEUE_CONNECTION=database
`;

function setupPusherEnv() {
    console.log('🔧 Setting up Pusher environment variables...\n');

    // Check if .env file exists
    if (!fs.existsSync(envPath)) {
        console.log('❌ .env file not found. Creating one...');
        
        // Try to copy from .env.example if it exists
        if (fs.existsSync(envExamplePath)) {
            fs.copyFileSync(envExamplePath, envPath);
            console.log('✅ Created .env from .env.example');
        } else {
            // Create basic .env file
            fs.writeFileSync(envPath, '# Laravel Environment Configuration\nAPP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n\n');
            console.log('✅ Created basic .env file');
        }
    }

    // Read current .env content
    let envContent = fs.readFileSync(envPath, 'utf8');

    // Check if Pusher config already exists
    if (envContent.includes('PUSHER_APP_KEY')) {
        console.log('⚠️  Pusher configuration already exists in .env file');
        console.log('   If you\'re having connection issues, check the values are correct.');
        return;
    }

    // Add Pusher configuration
    envContent += pusherConfig;

    // Write updated .env file
    fs.writeFileSync(envPath, envContent);

    console.log('✅ Added Pusher configuration to .env file');
    console.log('\n📋 Next steps:');
    console.log('   1. Run: npm run build');
    console.log('   2. Run: php artisan config:clear');
    console.log('   3. Run: php artisan cache:clear');
    console.log('   4. Start queue worker: php artisan queue:work');
    console.log('   5. Hard refresh browser (Ctrl+F5)');
    console.log('\n🎉 Pusher setup complete!');
}

// Run the setup
setupPusherEnv();

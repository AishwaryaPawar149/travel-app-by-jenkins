pipeline {
    agent any
    
    environment {
        // SSH Configuration
        SSH_CRED = 'node-app-key'
        TARGET_HOST = '65.1.241.184'
        TARGET_USER = 'ubuntu'
        
        // Application Paths
        APP_DIR = '/home/ubuntu/travelapp'
        BACKEND_DIR = "${APP_DIR}/backend"
        FRONTEND_DIR = "${APP_DIR}/frontend"
        
        // Repository
        REPO_URL = 'https://github.com/AishwaryaPawar149/travel-app-by-jenkins'
        BRANCH = 'master'
        
        // AWS Configuration
        AWS_REGION = 'ap-south-1'
        S3_BUCKET = 'travel-memory-bucket-by-aish'
        
        // Database Configuration
        DB_URL = 'jdbc:mysql://database-1.cdwkuiksmrsm.ap-south-1.rds.amazonaws.com:3306/travelapp'
        DB_USER = 'root'
        DB_PASSWORD = 'aishwarya149'
        
        // Application Configuration
        BACKEND_PORT = '8080'
        FRONTEND_PORT = '3000'
    }
    
    stages {
        stage('Clean Workspace') {
            steps {
                echo '🧹 Cleaning workspace...'
                cleanWs()
            }
        }
        
        stage('Clone Repository') {
            steps {
                echo '📦 Cloning repository...'
                git branch: "${BRANCH}", url: "${REPO_URL}"
            }
        }
        
        stage('Build Backend with Maven') {
            steps {
                echo '🔨 Building Spring Boot application...'
                dir('travelmemories-backend') {
                    sh '''
                        mvn clean package -DskipTests
                        echo "✅ JAR file created: $(ls -lh target/*.jar)"
                    '''
                }
            }
        }
        
        stage('Prepare Target Server') {
            steps {
                echo '📁 Creating directories on target server...'
                sshagent(credentials: ["${SSH_CRED}"]) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${TARGET_USER}@${TARGET_HOST} '
                            mkdir -p ${BACKEND_DIR}
                            mkdir -p ${FRONTEND_DIR}
                            mkdir -p ${APP_DIR}/logs
                            echo "✅ Directories created"
                        '
                    """
                }
            }
        }
        
        stage('Deploy Backend') {
            steps {
                echo '🚀 Deploying backend to target server...'
                sshagent(credentials: ["${SSH_CRED}"]) {
                    sh """
                        # Stop existing application
                        ssh ${TARGET_USER}@${TARGET_HOST} '
                            if pgrep -f "travelmemories.*jar" > /dev/null; then
                                echo "Stopping existing application..."
                                pkill -f "travelmemories.*jar" || true
                                sleep 5
                            fi
                        '
                        
                        # Copy JAR file
                        scp travelmemories-backend/target/*.jar ${TARGET_USER}@${TARGET_HOST}:${BACKEND_DIR}/travelmemories.jar
                        
                        echo "✅ Backend JAR copied successfully"
                    """
                }
            }
        }
        
        stage('Deploy Frontend') {
            steps {
                echo '🎨 Deploying frontend...'
                sshagent(credentials: ["${SSH_CRED}"]) {
                    sh """
                        # Copy frontend files
                        scp -r travelmemories-frontend/* ${TARGET_USER}@${TARGET_HOST}:${FRONTEND_DIR}/
                        
                        echo "✅ Frontend files copied successfully"
                    """
                }
            }
        }
        
        stage('Create Systemd Service') {
            steps {
                echo '⚙️ Creating systemd service...'
                sshagent(credentials: ["${SSH_CRED}"]) {
                    sh """
                        ssh ${TARGET_USER}@${TARGET_HOST} '
                            # Create systemd service file
                            sudo tee /etc/systemd/system/travelapp.service > /dev/null <<EOF
[Unit]
Description=Travel Memories Spring Boot Application
After=syslog.target network.target

[Service]
User=${TARGET_USER}
WorkingDirectory=${BACKEND_DIR}
ExecStart=/usr/bin/java -jar ${BACKEND_DIR}/travelmemories.jar
SuccessExitStatus=143
StandardOutput=append:${APP_DIR}/logs/application.log
StandardError=append:${APP_DIR}/logs/error.log
Restart=always
RestartSec=10

Environment="SPRING_DATASOURCE_URL=${DB_URL}"
Environment="SPRING_DATASOURCE_USERNAME=${DB_USER}"
Environment="SPRING_DATASOURCE_PASSWORD=${DB_PASSWORD}"
Environment="AWS_REGION=${AWS_REGION}"
Environment="AWS_S3_BUCKET_NAME=${S3_BUCKET}"

[Install]
WantedBy=multi-user.target
EOF
                            
                            # Reload systemd and enable service
                            sudo systemctl daemon-reload
                            sudo systemctl enable travelapp.service
                            
                            echo "✅ Systemd service created"
                        '
                    """
                }
            }
        }
        
        stage('Start Backend Application') {
            steps {
                echo '▶️ Starting backend application...'
                sshagent(credentials: ["${SSH_CRED}"]) {
                    sh """
                        ssh ${TARGET_USER}@${TARGET_HOST} '
                            sudo systemctl restart travelapp.service
                            sleep 10
                            sudo systemctl status travelapp.service
                        '
                    """
                }
            }
        }
        
        stage('Setup Nginx for Frontend') {
            steps {
                echo '🌐 Configuring Nginx...'
                sshagent(credentials: ["${SSH_CRED}"]) {
                    sh """
                        ssh ${TARGET_USER}@${TARGET_HOST} '
                            # Install Nginx if not present
                            if ! command -v nginx &> /dev/null; then
                                sudo apt-get update
                                sudo apt-get install -y nginx
                            fi
                            
                            # Create Nginx config
                            sudo tee /etc/nginx/sites-available/travelapp > /dev/null <<EOF
server {
    listen 80;
    server_name _;
    
    # Frontend
    location / {
        root ${FRONTEND_DIR};
        index index.html;
        try_files \\$uri \\$uri/ /index.html;
    }
    
    # Backend API
    location /api/ {
        proxy_pass http://localhost:${BACKEND_PORT};
        proxy_set_header Host \\$host;
        proxy_set_header X-Real-IP \\$remote_addr;
        proxy_set_header X-Forwarded-For \\$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \\$scheme;
    }
}
EOF
                            
                            # Enable site and restart Nginx
                            sudo ln -sf /etc/nginx/sites-available/travelapp /etc/nginx/sites-enabled/
                            sudo rm -f /etc/nginx/sites-enabled/default
                            sudo nginx -t
                            sudo systemctl restart nginx
                            sudo systemctl enable nginx
                            
                            echo "✅ Nginx configured and restarted"
                        '
                    """
                }
            }
        }
        
        stage('Health Check') {
            steps {
                echo '🏥 Performing health checks...'
                sshagent(credentials: ["${SSH_CRED}"]) {
                    sh """
                        ssh ${TARGET_USER}@${TARGET_HOST} '
                            echo "Waiting for application to start..."
                            sleep 15
                            
                            # Check if backend is running
                            if curl -f http://localhost:${BACKEND_PORT}/api/memories > /dev/null 2>&1; then
                                echo "✅ Backend is healthy!"
                            else
                                echo "❌ Backend health check failed!"
                                sudo systemctl status travelapp.service
                                tail -n 50 ${APP_DIR}/logs/error.log
                                exit 1
                            fi
                            
                            # Check if Nginx is running
                            if sudo systemctl is-active --quiet nginx; then
                                echo "✅ Nginx is running!"
                            else
                                echo "❌ Nginx is not running!"
                                exit 1
                            fi
                        '
                    """
                }
            }
        }
        
        stage('Display Deployment Info') {
            steps {
                echo '📊 Deployment Summary'
                sshagent(credentials: ["${SSH_CRED}"]) {
                    sh """
                        ssh ${TARGET_USER}@${TARGET_HOST} '
                            echo "================================"
                            echo "🎉 DEPLOYMENT SUCCESSFUL!"
                            echo "================================"
                            echo "Frontend URL: http://${TARGET_HOST}"
                            echo "Backend API: http://${TARGET_HOST}/api/memories"
                            echo "Backend Port: ${BACKEND_PORT}"
                            echo "================================"
                            echo "Service Status:"
                            sudo systemctl status travelapp.service --no-pager | head -n 5
                            echo "================================"
                            echo "Recent Logs:"
                            tail -n 20 ${APP_DIR}/logs/application.log
                            echo "================================"
                        '
                    """
                }
            }
        }
    }
    
    post {
        success {
            echo '✅ ========================================='
            echo '✅ BUILD & DEPLOYMENT SUCCESSFUL! 🎉'
            echo '✅ ========================================='
            echo "✅ Application URL: http://${TARGET_HOST}"
            echo "✅ API Endpoint: http://${TARGET_HOST}/api/memories"
            echo '✅ ========================================='
        }
        failure {
            echo '❌ ========================================='
            echo '❌ BUILD OR DEPLOYMENT FAILED!'
            echo '❌ ========================================='
            echo '❌ Check logs above for error details'
            echo '❌ ========================================='
        }
        always {
            echo '🧹 Cleaning up workspace...'
            cleanWs()
        }
    }
}
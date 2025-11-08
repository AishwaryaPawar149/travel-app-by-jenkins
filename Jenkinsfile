pipeline {
    agent any

    environment {
        SSH_CRED = 'ubuntu'          // Jenkins credential ID containing your EC2 private key
        SERVER_IP = '65.2.136.169'   // New EC2 instance public IP
        REMOTE_USER = 'ubuntu'       // Default user for Ubuntu EC2
        WEB_DIR = '/var/www/html/travel-memory-app'  // Deployment directory
    }

    stages {
        stage('Clone Repository') {
            steps {
                echo "Cloning the master branch from GitHub repository..."
                git branch: 'master', url: 'https://github.com/AishwaryaPawar149/travel-app-by-jenkins.git'
            }
        }

        stage('Deploy to Target Server') {
            steps {
                echo "Deploying project files to target EC2 server..."
                sshagent (credentials: ["${SSH_CRED}"]) {
                    sh '''
                    ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${SERVER_IP} "
                        sudo mkdir -p ${WEB_DIR} &&
                        sudo rm -rf ${WEB_DIR}/* &&
                        sudo chown -R ${REMOTE_USER}:${REMOTE_USER} ${WEB_DIR}
                    "
                    scp -r * ${REMOTE_USER}@${SERVER_IP}:${WEB_DIR}/
                    ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${SERVER_IP} "
                        sudo systemctl restart apache2
                    "
                    '''
                }
            }
        }
    }

    post {
        success {
            echo "✅ Deployment successful! Your PHP app is live on http://${SERVER_IP}/"
        }
        failure {
            echo "❌ Deployment Failed! Please check the Jenkins logs."
        }
    }
}

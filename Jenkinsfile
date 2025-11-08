pipeline {
    agent any

    environment {
        SSH_CRED = 'node-app-key'
        SERVER_IP = '65.2.136.169'
        REMOTE_USER = 'ubuntu'
        WEB_DIR = '/var/www/html/travel-memory-app'
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
                    # Clean target directory and recreate it
                    ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${SERVER_IP} "
                        sudo mkdir -p ${WEB_DIR} &&
                        sudo rm -rf ${WEB_DIR}/* &&
                        sudo chown -R ${REMOTE_USER}:${REMOTE_USER} ${WEB_DIR}
                    "

                    # Copy project files
                    scp -o StrictHostKeyChecking=no -r * ${REMOTE_USER}@${SERVER_IP}:${WEB_DIR}/

                    # Verify deployment
                    ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${SERVER_IP} "
                        echo 'Deployment completed successfully on server:' &&
                        ls -la ${WEB_DIR}
                    "
                    '''
                }
            }
        }
    }

    post {
        success {
            echo "✅ Deployment Successful!"
        }
        failure {
            echo "❌ Deployment Failed! Please check the Jenkins logs."
        }
    }
}

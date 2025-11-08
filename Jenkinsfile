pipeline {
    agent any

    environment {
        SSH_CRED = 'node-app-key'
        SERVER_IP = '3.110.25.70'
        REMOTE_USER = 'ubuntu'
        WEB_DIR = '/var/www/html/travel-memory-app'
    }

    stages {
        stage('Clone Repository') {
            steps {
                git branch: 'main', url: 'https://github.com/AishwaryaPawar149/travel-app-by-jenkins.git'
            }
        }

        stage('Deploy to Target Server') {
            steps {
                sshagent (credentials: ["${SSH_CRED}"]) {
                    sh '''
                    ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${SERVER_IP} '
                        sudo rm -rf ${WEB_DIR}/* &&
                        mkdir -p ${WEB_DIR} &&
                        exit
                    '
                    scp -r * ${REMOTE_USER}@${SERVER_IP}:${WEB_DIR}/
                    '''
                }
            }
        }
    }
}

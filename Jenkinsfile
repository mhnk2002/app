pipeline {
    agent any

    environment {
        SWARM_STACK_NAME = 'app'
        FRONTEND_URL = 'http://192.168.0.1:8080'
    }
    
    stages {
        stage('Checkout') {
            steps { checkout scm }
        }

        stage('Build Docker Images') {
            steps {
                sh "docker build --no-cache -f php.Dockerfile -t mhnk2002/crudback:latest ."
                sh "docker build --no-cache -f mysql.Dockerfile -t mhnk2002/mysql:latest ."
            }
        }

        stage('Push Images') {
            steps {
                sh "docker push mhnk2002/crudback:latest"
                sh "docker push mhnk2002/mysql:latest"
            }
        }

        stage('Deploy') {
            steps {
                sh """
                    docker service update --force app_web-server
                    docker service update --force app_db
                    docker stack deploy --with-registry-auth -c docker-compose.yaml ${SWARM_STACK_NAME}
                """
            }
        }

        stage('Run JSON Validation Tests') {
            steps {
                sleep 10
                sh "chmod +x tests/json_validation_test.sh && ./tests/json_validation_test.sh"
            }
        }
    }

    post {
        success { echo '✅ Pipeline completed successfully!' }
        failure { echo '❌ Pipeline failed!' }
    }
}

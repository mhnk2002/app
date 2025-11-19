pipeline {
    agent any

    environment {
        SWARM_STACK_NAME = 'app'
        DB_SERVICE = 'db'
        DB_USER = 'root'
        DB_PASSWORD = 'secret'
        DB_NAME = 'dbook'
        FRONTEND_URL = 'http://192.168.0.1:8080'
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Build Docker Images') {
            steps {
                script {
                    sh "docker build -f php.Dockerfile -t mhnk2002/crudback ."
                    sh "docker build -f mysql.Dockerfile -t mhnk2002/mysql ."
                } 
            }
        }
        
        stage('Deploy to Docker Swarm') {
            steps {
                script {
                    sh '''
                        if ! docker info | grep -q "Swarm: active"; then
                            docker swarm init || true
                        fi
                    '''
                    sh "docker stack deploy --with-registry-auth -c docker-compose.yaml ${SWARM_STACK_NAME}"
                }
            }
        }
        
        stage('Run Tests') {
            steps {
                script {
                    echo 'Ожидание запуска сервисов...'
                    sleep time: 30, unit: 'SECONDS'

                    echo 'Проверка доступности фронтенда...'
                    sh """
                        if ! curl -fsS ${FRONTEND_URL}; then
                            echo 'Фронтенд недоступен'
                            exit 1
                        fi
                    """
                    
                    echo 'Получение ID контейнера базы данных...'
                    def dbContainerId = sh(
                        script: "docker ps --filter name=${SWARM_STACK_NAME}_${DB_SERVICE} --format '{{.ID}}'",
                        returnStdout: true
                    ).trim()

                    if (!dbContainerId) {
                        error("Контейнер базы данных не найден")
                    }
 
                    echo 'Подключение к MySQL и проверка таблиц...'
                    sh """
                        docker exec ${dbContainerId} mysql -u${DB_USER} -p${DB_PASSWORD} -e 'USE ${DB_NAME}; SHOW TABLES;'
                    """
                }
            }
        }
        
        stage('JSON Validation Tests') {
            steps {
                script {
                    echo '🔍 Запуск JSON валидационных тестов...'
                    sleep time: 15, unit: 'SECONDS'
                    
                    try {
                        sh """
                            cd /var/jenkins_home/workspace/CRUD-App-CI-CD
                            chmod +x tests/json_validation_test.sh
                            ./tests/json_validation_test.sh
                        """
                        echo '✅ Все JSON валидационные тесты пройдены! API корректно проверяет обязательные поля.'
                    } catch (Exception e) {
                        error('❌ JSON валидационные тесты провалились! API не проверяет обязательные поля.')
                    }
                }
            }
        }
    }

    post {
        success {
            echo '✅ Все этапы успешно завершены!'
        }
        failure {
            echo '❌ Ошибка в одном из этапов. Проверьте логи выше.'
        } 
        always {
            cleanWs()
        }
    }
}

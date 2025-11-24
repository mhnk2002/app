pipeline {
    agent any

    environment {
	SWARM_STACK_NAME = 'app'
	DB_SERVICE = 'db'
	DB_USER = 'root'
	DB_PASSWORD= 'secret'
	DB_NAME= 'dbook'
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
		    sh "docker build -f php.Dockerfile -t mhnk2002/cruback1 ."
		    sh "docker build -f mysql.Dockerfile -t mhnk2002/mysql1 ."
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
		    echo 'Waiting services play...'
		    sleep time: 30, unit: 'SECONDS'

		    echo 'Test front...'
		    sh """
			if ! curl -fsS ${FRONTEND_URL}; then
			    echo 'Front lost'
			    exit 1
			fi
		    """
		    
		    echo 'Geting ID container of DB...'
		    def dbContainerId = sh(
			script: "docker ps --filter name=${SWARM_STACK_NAME}_${DB_SERVICE} --format '{{.ID}}'",
			returnStdout: true
		    ).trim()

		    if (!dbContainerId) {
			error("Container of db not found")
		    }
 
		    echo 'Connecting to MySQL and checking tables...'
		    sh """
			docker exec ${dbContainerId} mysql -u${DB_USER} -p${DB_PASSWORD} -e 'USE ${DB_NAME}; SHOW TABLES;'
		    """
		}
	    }
	}
    }

    post {
	success {
	    echo 'Все этапы завершены'
	}
	failure {
	    echo 'Ошибка в одном из этапов. Проверь логи выше'
	}	
	always {
	    cleanWs()
	}
    }
}

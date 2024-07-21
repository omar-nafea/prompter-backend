pipeline {
  agent any
  stages {
    stage('move-to-prompter-server'){
    steps {
      sshagent(['prompter-server']) {
        sh 'rsync * prompter@198.7.113.119:~/back'
        }
    }
      
    }
    stage('install-packages'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader"'
        }
    }
    }
    stage('remove-cache'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && php artisan optimize:clear"'
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo chmod -R 777 storage bootstrap/cache"'
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo chown -R prompter:www-data back & sudo nginx -s reload"'
        }
    }
    }
}
  
}

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
    stage('backup database'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && php artisan backup:run --only-db"'
        }
    }
    }
    stage('queue restart'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo chmod -R 777 storage bootstrap/cache && php artisan queue:restart && php artisan down"'
        }
    }
    }
    stage('install-packages'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && composer install && php artisan optimize:clear"'
        }
    }
    }
    stage('migrate'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && php artisan migrate --force"'
        }
    }
    }
    stage('composer install -no-dev'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader && php artisan optimize:clear"'
        }
    }
    }
    stage('schedule interrupt'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && php artisan schedule:interrupt"'
        }
    }
    }
    stage('restart supervisor'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl restart all"'
        }
    }
    }
    stage('server up'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo chmod -R 777 storage bootstrap/cache"'
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo chown -R prompter:www-data back & sudo nginx -s reload"'
        sh 'ssh  prompter@198.7.113.119 "cd back && php artisan up"'
        }
    }
    }
}
}

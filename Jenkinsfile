pipeline {
  agent any
  stages {
    stage('move-to-prompter-server'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && echo \\"Build start at: $(date)\\">> ~/back/build.log "'
        sh 'rsync -rvu * prompter@198.7.113.119:~/back >> ~/back/build.log 2>&1'
        }
    }
    }
    stage('backup database'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo chmod -R 775 storage bootstrap/cache && php artisan backup:run --only-db"'
        }
    }
    }
    stage('queue restart'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && php artisan queue:restart && php artisan down"'
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
        sh 'ssh  prompter@198.7.113.119 "cd back && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader && sudo chmod -R 775 storage bootstrap/cache"'
        }
    }
    }
    stage('cache'){
    steps {
      sshagent(['prompter-server']) {
        sh 'ssh  prompter@198.7.113.119 "cd back && php artisan optimize && php artisan app:cache-data && php artisan queue:restart"'
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
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo chmod -R 775 storage bootstrap/cache"'
        sh 'ssh  prompter@198.7.113.119 "cd back && sudo chown -R prompter:www-data back & sudo nginx -s reload"'
        sh 'ssh  prompter@198.7.113.119 "cd back && php artisan up"'
        }
    }
    }
}
post {
    success {
        sshagent(['prompter-server']) {
            sh 'ssh prompter@198.7.113.119 "cd back && echo \\"Build successfully at: $(date)\\" >> ~/back/build.log"'
        }
    }
    failure {
        sshagent(['prompter-server']) {
            sh 'ssh prompter@198.7.113.119 "cd back && echo \\"Build failed at: $(date)\\" >> ~/back/build.log"'
        }
    }
}
}

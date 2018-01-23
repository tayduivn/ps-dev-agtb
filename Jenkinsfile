podTemplate(
    label: "mango-${env.BRANCH_NAME}-${env.BUILD_NUMBER}",
    containers: [
      containerTemplate(name: 'karma', image: 'registry.sugarcrm.net/karma/karma:latest',ttyEnabled: true, args: 'cat'),
    ]
) {
    node("mango-${env.BRANCH_NAME}-${env.BUILD_NUMBER}") {
      stage('checkout') {
        checkout scm
      }
      stage('css lint') {
        container('karma') {
          dir("sugarcrm") {
            sh """
            yarn install
            node_modules/gulp/bin/gulp.js lint:css
            """
          }
        }
      }
    }
}

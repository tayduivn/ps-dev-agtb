/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 1/31/12
 * Time: 12:26 PM
 * To change this template use File | Settings | File Templates.
 */
fixtures = {
    videoData: {
          valid: { // response starts here
            "status": "200",
            "version": "1.0",
            "response": {
              "videos": [
                {
                  "id": 1,
                  "title": "Cat plays piano"
                },
                {
                  "id": 2,
                  "title": "Dramatic Chipmunk"
                }
              ]
            }
          },
        invalid: {
            "status": "404",
                        "version": "1.0",
                        "response": {
                          "error": [
                            {
                              "number": 404,
                              "title": "We have looked far and wide and can not find your video"
                            }
                          ]
                        }
        }
    }
};
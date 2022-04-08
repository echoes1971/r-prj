
const app_cfg = {
     endpoints: [ ["http://localhost:8080/jsonserver.php","Local"],
                    ["https://www.roccoangeloni.it/rproject/jsonserver.php","RRA"],
                    ["https://echocloud.doesntexist.com/jsonserver.php","Echo Cloud"],
                    ["https://www.africa-film.com/jsonserver.php","Africa Film"]
                ]
    ,endpoint: "http://localhost:8080/jsonserver.php"
    // This path is where is stored the react-app, i.e. /myapp/
    // IT MUST ALWAYS END WITH /  !!!!!
    ,root_path: "/"
    ,dark_theme: false
    // Groups
    ,GROUP_ADMIN: '-2'
    ,GROUP_USERS: '-3'
    ,GROUP_GUESTS: '-4'
    ,GROUP_PROJECT: '-5'
    ,GROUP_WEBMASTER: '-6'
    };

export { app_cfg }

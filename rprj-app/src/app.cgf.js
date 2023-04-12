
const app_cfg = {
     endpoints: [ ["http://localhost:8080/jsonserver.php","Local"],
                    ["https://rprj.roccoangeloni.ch/php/jsonserver.php","rprj"],
                    ["https://www.roccoangeloni.it/rproject/jsonserver.php","RRA"],
                    ["https://echocloud.doesntexist.com/jsonserver.php","Echo Cloud"],
                    ["https://www.africa-film.com/jsonserver.php","Africa Film"]
                ]
    ,endpoint: process.env.NODE_ENV !== 'production' ? "http://localhost:8080/jsonserver.php" : "https://rprj.roccoangeloni.ch/jsonserver.php"
    ,endpoint_download: process.env.NODE_ENV !== 'production' ? "http://localhost:8080/download.php" : "https://rprj.roccoangeloni.ch/download.php"
    // This path is where is stored the react-app, i.e. /myapp/
    // IT MUST ALWAYS END WITH /  !!!!!
    ,root_path: process.env.NODE_ENV !== 'production' ? "/" : "/app/"
    ,dark_theme: process.env.NODE_ENV !== 'production' ? false : true
    // Groups
    ,GROUP_ADMIN: '-2'
    ,GROUP_USERS: '-3'
    ,GROUP_GUESTS: '-4'
    ,GROUP_PROJECT: '-5'
    ,GROUP_WEBMASTER: '-6'
    };

export { app_cfg }


const app_cfg = {
     endpoints: [ ["http://localhost:8080/jsonserver.php","Local"],
                    ["https://www.roccoangeloni.it/rproject/jsonserver.php","RRA"],
                    ["https://echocloud.doesntexist.com/jsonserver.php","Echo Cloud"],
                    ["https://www.africa-film.com/jsonserver.php","Africa Film"]
                ]
    ,endpoint: "http://localhost:8080/jsonserver.php"
    // This path is where is stored the react-app, i.e. /myapp/
    ,root_path: "/"
    ,dark_theme: false
};

export { app_cfg }

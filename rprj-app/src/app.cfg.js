
const ROOT_PATH = "/app/" // process.env.NODE_ENV !== 'production' ? "/" : "/app/"
const app_cfg = {
    site_title: process.env.REACT_APP_SITE_TITLE > '' ? process.env.REACT_APP_SITE_TITLE : 'R-Prj'
     ,endpoints: [ ["http://localhost:8080/jsonserver.php","Local"],
                    ["https://rprj.roccoangeloni.ch/php/jsonserver.php","rprj"],
                    ["https://www.roccoangeloni.it/rproject/jsonserver.php","RRA"],
                    ["https://www.africa-film.com/jsonserver.php","Africa Film"]
                ]
    ,endpoint: process.env.REACT_APP_ENDPOINT > '' ? process.env.REACT_APP_ENDPOINT + "/jsonserver.php" : window.location.href.split(ROOT_PATH)[0] + "/jsonserver.php" // process.env.NODE_ENV !== 'production' ? "http://localhost:8080/jsonserver.php" : "https://rprj.roccoangeloni.ch/jsonserver.php"
    ,endpoint_download: process.env.REACT_APP_ENDPOINT > '' ? process.env.REACT_APP_ENDPOINT + "/download.php" : window.location.href.split(ROOT_PATH)[0] + "/download.php"
    // This path is where is stored the react-app, i.e. /myapp/
    // IT MUST ALWAYS END WITH /  !!!!!
    ,root_path: ROOT_PATH
    ,dark_theme: false // process.env.NODE_ENV !== 'production' ? false : true
    // Groups
    ,GROUP_ADMIN: '-2'
    ,GROUP_USERS: '-3'
    ,GROUP_GUESTS: '-4'
    ,GROUP_PROJECT: '-5'
    ,GROUP_WEBMASTER: '-6'
    };

export { app_cfg }

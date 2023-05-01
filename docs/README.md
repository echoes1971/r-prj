
# R-Project

Hi :-)<br />
Welcome to my old pet project.

The easiest way to test it, is to run the following command:
```
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up
```

and point your browser to:

- http://localhost:8080 for the traditional web interface in php
- http://localhost:3000 for the new reactjs UI (still in progress)

More docs to come...

# Tech infos

## Directories

- Ansible: ansible script to automate the deployment
- config: at the moment it contains a simple mysql config file for compatibility
- docker: **deprecated**, except `webentrypoint.sh`
- docs: self explainatory
- k8s: experiments with kubernetes
- php: the main UI and Back-End
- python: **deprecated**, desktop client using QT; it's still written in python2 so it is unusable at the moment.
- rprj-app: the brand new sparkling UI written with React-JS (still work in progress)

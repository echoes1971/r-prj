#!/bin/bash

#minikube docker-env --shell='bash'
export DOCKER_TLS_VERIFY="1"
export DOCKER_HOST="tcp://192.168.99.100:2376"
export DOCKER_CERT_PATH="/home/roberto/.kube/certs"
export MINIKUBE_ACTIVE_DOCKERD="minikube"

# env | grep DOCKER

docker image ls | grep rprj

echo "Kubernetes ready"
echo
# echo << __EOF
# Go to the docker directory and run:
# ./docker.sh images
# to build the images.
# Then come back here
# __EOF

# \[\033[COLORm\]
# Black: 30
# Blue: 34
# Cyan: 36
# Green: 32
# Purple: 35
# Red: 31
# White: 37
# Yellow: 33

bash --rcfile <(cat ~/.bashrc; echo 'PS1="\[\033[33m\](k8s) $PS1"')


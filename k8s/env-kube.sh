#!/bin/bash

#minikube docker-env --shell='bash'
export DOCKER_TLS_VERIFY="1"
export DOCKER_HOST="tcp://192.168.99.100:2376"
export DOCKER_CERT_PATH="/home/roberto/.kube/certs"
export MINIKUBE_ACTIVE_DOCKERD="minikube"

env | grep DOCKER


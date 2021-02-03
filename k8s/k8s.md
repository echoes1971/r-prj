
# k8s

## Install kubectl

```
curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl"
mkdir -p ~/.local/bin/kubectl
mv ./kubectl ~/.local/bin/kubectl
```

## Prepare the .kube directory

~/.kube

- config
- certs: copia della directory **C:\Users\rober\.minikube\certs**
  - ca-key.pem
  - ca.pem
  - cert.pem
  - key.pem
- localkube: copia della directory **C:\Users\rober\.minikube\profiles\minikube**, in particolare
  - ca.crt
  - client.crt
  - client.key

## Set ENV variables

```
export DOCKER_TLS_VERIFY="1"
export DOCKER_HOST="tcp://192.168.99.100:2376"
export DOCKER_CERT_PATH="/home/roberto/.kube/certs"
export MINIKUBE_ACTIVE_DOCKERD="minikube"

env | grep DOCKER
```


# References

- [Install kubectl](https://kubernetes.io/docs/tasks/tools/install-kubectl/)
- [How to Run Locally Built Docker Images in Kubernetes](https://medium.com/swlh/how-to-run-locally-built-docker-images-in-kubernetes-b28fbc32cc1d)
- [Deploying WordPress and MySQL with Persistent Volumes](https://kubernetes.io/docs/tutorials/stateful-application/mysql-wordpress-persistent-volume/)

- [Persistent Volumes](https://kubernetes.io/docs/concepts/storage/persistent-volumes/)
  - [local](https://kubernetes.io/docs/concepts/storage/volumes/#local)

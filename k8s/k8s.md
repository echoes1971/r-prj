
# k8s

## minikube

```
minikube start --mount-string .\Projects\k8s:/data --mount
minikube start
minikube stop

minikube ssh

minikube service list

minikube service rprj-php-mariadb --url
```

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

## Commands

```
kubectl apply -f rprj_pvc.yaml
kubectl apply -f rprj_db.yaml
kubectl apply -f rprj_fe.yaml

kubectl delete -f rprj_fe.yaml
kubectl delete -f rprj_db.yaml
kubectl delete -f rprj_pvc.yaml

kubectl get nodes
kubectl get namespace
kubectl get pods
kubectl get pods -o wide
kubectl get pv
kubectl describe pv
kubectl get pvc
kubectl get deployments
kubectl get services
kubectl get ingresses
kubectl describe ingress rprj-php-mariadb-ingress

kubectl logs -f deployment.apps/rprj-mariadb -c rprj-mariadb
kubectl logs -f deployment.apps/rprj-php-mariadb -c rprj-php-mariadb

kubectl exec my-pod -c my-container -- ls /
kubectl exec --stdin --tty deployment.apps/rprj-php-mariadb -c rprj-php-mariadb -- /bin/bash
kubectl exec --stdin --tty deployment.apps/rprj-mariadb -c rprj-mariadb -- /bin/bash
kubectl exec --stdin --tty deployment.apps/rprj-mariadb -c rprj-mariadb -- mysql -pmysecret

kubectl exec -ti deployment.apps/rprj-php-mariadb -c rprj-php-mariadb -- curl localhost:80

# ReplicaSets
kubectl get rs
kubectl scale deployment rprj-php-mariadb --replicas=4
kubectl describe deployment rprj-php-mariadb
kubectl scale deployment rprj-php-mariadb --replicas=1

kubectl config set-context --current --namespace=<insert-namespace-name-here>

```

# References

- [Install kubectl](https://kubernetes.io/docs/tasks/tools/install-kubectl/)
- [How to Run Locally Built Docker Images in Kubernetes](https://medium.com/swlh/how-to-run-locally-built-docker-images-in-kubernetes-b28fbc32cc1d)
- [Deploying WordPress and MySQL with Persistent Volumes](https://kubernetes.io/docs/tutorials/stateful-application/mysql-wordpress-persistent-volume/)

- [Persistent Volumes](https://kubernetes.io/docs/concepts/storage/persistent-volumes/)
  - [local](https://kubernetes.io/docs/concepts/storage/volumes/#local)

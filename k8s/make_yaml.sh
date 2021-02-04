#!/bin/bash

PRJ_HOME=`cd ..; pwd`

. $PRJ_HOME/docker/docker.config

cat <<EOF >./rprj_db.yaml
apiVersion: v1
kind: Service
metadata:
  name: $MYSQL_APP
  labels:
    app: $PHP_APP
spec:
  ports:
    - port: 3306
  selector:
    app: $PHP_APP
    tier: rprj-db
  clusterIP: None
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: $MYSQL_APP
  labels:
    app: $PHP_APP
spec:
  replicas: 1
  selector:
    matchLabels:
      app: $PHP_APP
      tier: rprj-db
  template:
    metadata:
      labels:
        app: $PHP_APP
        tier: rprj-db
    spec:
      containers:
      - name: $MYSQL_APP
        image: $RPRJ_IMG_DB
        imagePullPolicy: Never
        env:
        - name: MYSQL_ROOT_PASSWORD
          value: $MYSQL_PASSWORD
        ports:
        - containerPort: 3306
          name: rprj-db
EOF

cat <<EOF >./rprj_fe.yaml
apiVersion: v1
kind: Service
metadata:
  name: $PHP_APP
  labels:
    app: $PHP_APP
spec:
  type: LoadBalancer
  ports:
    - port: 80
      protocol: TCP
  selector:
    app: $PHP_APP
    tier: frontend
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: $PHP_APP
  labels:
    app: $PHP_APP
spec:
  replicas: 1
  selector:
    matchLabels:
      app: $PHP_APP
      tier: frontend
  template:
    metadata:
      labels:
        app: $PHP_APP
        tier: frontend
    spec:
      containers:
      - name: $PHP_APP
        image: $RPRJ_IMG
        imagePullPolicy: Never
        env:
        - name: MYSQL_APP
          value: $MYSQL_APP
        - name: MYSQL_ROOT_PASSWORD
          value: $MYSQL_PASSWORD
        ports:
        - containerPort: 8080
          #name: $PHP_APP
EOF

# cat <<EOF >./rprj.yaml
# apiVersion: v1
# resources:
#   - rprj_db.yaml
#   - rprj_fe.yaml
# EOF


echo "kubectl get services $PHP_APP"
echo "minikube service $PHP_APP --url"



#!/bin/bash

PRJ_HOME=`cd ..; pwd`

. $PRJ_HOME/docker/docker.config

cat <<EOF >./rprj_pvc.yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: $MYSQL_APP-pvc
  labels:
    app: $PHP_APP
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 5Gi #5 GB
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: $PHP_APP-pvc
  labels:
    app: $PHP_APP
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 10Gi #5 GB
EOF

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
        volumeMounts:
        - name: $MYSQL_APP-persistent-storage
          mountPath: /var/lib/mysql
      volumes:
      - name: $MYSQL_APP-persistent-storage
        persistentVolumeClaim:
          claimName: $MYSQL_APP-pvc
EOF

cat <<EOF >./rprj_fe.yaml
# apiVersion: networking.k8s.io/v1beta1
# kind: Ingress
# metadata:
#   name: $PHP_APP
#   annotations:
#     kubernetes.io/ingress.class: "nginx"
#     cert-manager.io/cluster-issuer: "letsencrypt-prod"
# spec:
#   tls:
#   - hosts:
#     - localhost
#     - $SERVER_NAME
#     secretName: $PHP_APP-tls
#   rules:
#   - host: localhost
#     http:
#       paths:
#       - backend:
#           serviceName: $PHP_APP
#           servicePort: 80
#   - host: $SERVER_NAME
#     http:
#       paths:
#       - backend:
#           serviceName: $PHP_APP
#           servicePort: 80
# ---
apiVersion: v1
kind: Service
metadata:
  name: $PHP_APP
  labels:
    app: $PHP_APP
spec:
#    type: NodePort
#    ports:
#    - port: 8080
#      nodePort: 30080
#      name: omninginx
  type: LoadBalancer
  ports:
    - port: 80
      nodePort: 30080
      #targetPort: 5678
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
        - containerPort: 5678
        volumeMounts:
        - name: $PHP_APP-persistent-storage
          mountPath: /var/www/html/files
        - name: $PHP_APP-persistent-storage
          mountPath: /var/www/html/mng/files
      volumes:
      - name: $PHP_APP-persistent-storage
        persistentVolumeClaim:
          claimName: $PHP_APP-pvc
EOF

# cat <<EOF >./rprj.yaml
# apiVersion: v1
# resources:
#   - rprj_db.yaml
#   - rprj_fe.yaml
# EOF


echo "kubectl get services $PHP_APP"
echo "minikube service $PHP_APP --url"
echo "minikube service $PHP_APP"



docker build -t triangle .
docker tag triangle ghcr.io/triangle-org/engine:latest
docker push ghcr.io/triangle-org/engine:latest
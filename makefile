docker_project_name = languagetool

docker_build:
	docker build --tag="debian:$(docker_project_name)" docker/.

docker_run:
	docker run -d -p 8010:8010 debian:$(docker_project_name)

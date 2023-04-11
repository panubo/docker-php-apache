NAME = php-apache
TAG = $(shell basename $(shell pwd))
IMAGE_NAME := panubo/$(NAME)

.PHONY: help build push clean bash run

help:
	@printf "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\\x1b[36m\1\\x1b[m:\2/' | column -c2 -t -s :)\n"

build: ## Builds docker image
	docker build --pull -t $(IMAGE_NAME):$(TAG) .

push: ## Push image to registry
	docker tag $(IMAGE_NAME):$(TAG) docker.io/$(IMAGE_NAME):latest
	docker push $(IMAGE_NAME):$(TAG)
	docker push $(IMAGE_NAME):latest

clean: ## Remove built image
	docker rmi $(IMAGE_NAME):$(TAG)

bash: ## Runs bash in the container
	docker run --rm -it -v $(shell pwd)/test:/srv/remote $(IMAGE_NAME):$(TAG) bash

run: ## Runs the container with test data
	docker run --rm -it -p 8000:8000 -v $(shell pwd)/test:/srv/remote --name $(NAME) $(IMAGE_NAME):$(TAG)

_ci_test:
	true

_ci_version:
	@echo $(TAG)

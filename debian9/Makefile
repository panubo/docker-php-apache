NAME = php-apache
TAG = $(shell basename $(shell pwd))
IMAGE_NAME := panubo/$(NAME)

.PHONY: help build push clean bash run

help:
	@printf "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\\x1b[36m\1\\x1b[m:\2/' | column -c2 -t -s :)\n"

build: ## Builds docker image
	docker build --pull -t $(IMAGE_NAME):$(TAG) .

push: ## Push image to registry
	docker push $(IMAGE_NAME):$(TAG)

clean: ## Remove built image
	docker rmi $(IMAGE_NAME):$(TAG)

bash: ## Runs bash in the container
	docker run --rm -it $(IMAGE_NAME):$(TAG) bash

run: ## Runs the container
	docker run --rm -it $(IMAGE_NAME):$(TAG)

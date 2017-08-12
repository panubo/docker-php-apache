TAG = $(shell basename $(shell pwd))
NAME = panubo/php-apache:$(TAG)

help:
	@printf "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\\x1b[36m\1\\x1b[m:\2/' | column -c2 -t -s :)\n"

bash: ## Runs bash in the container
	docker run --rm -it $(NAME) bash

build: ## Builds docker image
	docker build -t $(NAME) .

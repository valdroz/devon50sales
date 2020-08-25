
build:
	docker build -t ocdevon .

ssh:
	docker rm ocdevon
	docker run -p 80:80 -p 3306:3306 -p 33060:33060 --name ocdevon -it ocdevon bash

run:
	docker rm ocdevon
	docker run -p 80:80 -p 3306:3306 --name ocdevon ocdevon
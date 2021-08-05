
build:
	docker build -t ocdevon .

run:
	PWD=$(pwd)
	docker rm ocdevon || true
	docker run -it -p 8080:80 -p 3306:3306 -v "${PWD}/oc/upload:/root/source" --name ocdevon ocdevon

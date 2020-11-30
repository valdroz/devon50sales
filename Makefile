
build:
	docker build -t ocdevon .

run:
	PWD=$(pwd)
	docker rm ocdevon
	docker run -it -p 80:80 -p 3306:3306 -v "${PWD}/www:/var/www" --name ocdevon ocdevon

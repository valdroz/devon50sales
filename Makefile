
build:
	podman build -t ocdevon .

run:
	PWD=$(pwd)
	podman rm ocdevon || true
	podman run -it -p 8080:80 -p 3306:3306 -v "${PWD}/oc/upload:/root/source" --name ocdevon ocdevon

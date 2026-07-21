handleiding:
	docker run --rm --volume "`pwd`:/data" --user `id -u`:`id -g` pandoc/extra:latest-ubuntu -N *.md  -M date="`date "+%d-%m-%Y"`" --from markdown --template eisvogel --listings -o verwerkingsregister_handleiding.pdf
	mv verwerkingsregister_handleiding.pdf ../../src/cms/public/pdf/verwerkingsregister_handleiding.pdf
	open ../../src/cms/public/pdf/verwerkingsregister_handleiding.pdf

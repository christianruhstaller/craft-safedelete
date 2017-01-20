build: js

js:
	./node_modules/.bin/webpack

watch-js:
	./node_modules/.bin/webpack --watch

.PHONY:
	js
	watch-js
# syntax = edrevo/dockerfile-plus

# We extend the official Sail image by using a combination of
# the docker-compose `context` and the "INCLUDE" directive
INCLUDE+ ./Dockerfile


ARG TARGETARCH
ARG HUGO_VERSION=0.162.1
ARG DART_SASS_VERSION=1.100.0

# Install Hugo  -  https://github.com/gohugoio/hugo/releases
RUN curl -LJO https://github.com/gohugoio/hugo/releases/download/v${HUGO_VERSION}/hugo_extended_${HUGO_VERSION}_linux-${TARGETARCH}.deb
RUN dpkg -i hugo_extended_${HUGO_VERSION}_linux-${TARGETARCH}.deb

# Install Dart Sass  -  https://github.com/sass/dart-sass/releases
# Note: We replace `amd64` with `x64` in the filename, as `dart-sass` doesn't provide an `amd64` version
RUN curl -LJO https://github.com/sass/dart-sass/releases/download/${DART_SASS_VERSION}/dart-sass-${DART_SASS_VERSION}-linux-$(echo "$TARGETARCH" | sed "s/amd/x/").tar.gz
RUN tar -xf dart-sass-${DART_SASS_VERSION}-linux-$(echo "$TARGETARCH" | sed "s/amd/x/").tar.gz
RUN cp -r dart-sass/* /usr/local/bin
RUN rm -rf dart-sass*

# install exif
RUN apt-get update && apt-get install -y exiftool

# PHP ini
RUN echo "zend.assertions=1" >>  /etc/php/8.4/cli/conf.d/999-development.ini

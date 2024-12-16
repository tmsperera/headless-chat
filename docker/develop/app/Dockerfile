FROM php:8.4.1-fpm

ARG CONTAINER_USER=laravel
ARG UID
ARG GID
ARG NODE_VERSION=20

#
# Create new group and assign given $GID to that new group.
# If group with GID exist it will not create new group.
#
# groupadd --help
# -f, --force           exit successfully if the group already exists, and cancel -g if the $GID is already used
# -g, --gid             GID use GID for the new group
#
RUN groupadd --force -g $GID $CONTAINER_USER

#
# Create new user and assign given $UID and $GID.
# Also will not create defult group since we created the group in previous command.
# If no group is created from previous command, it means that there is a group in the system already.
# All we care is gid and uid not the user name or group name.
# Regardless the new group creaded or not the new user will be assigned to the provided $UID and $GID
#
# useradd --help
# -N, --no-user-group   do not create a group with the same name as the user
# -m, --create-home     create the user's home directory
# -s, --shell SHELL     login shell of the new account
# -g, --gid GROUP       name or ID of the primary group of the new account
# -u, --uid UID         user ID of the new account
#
RUN useradd -ms /bin/bash --no-user-group -g $GID -u $UID $CONTAINER_USER

#
# Unix user/group of the child processes. This can be used only if the master
# process running user is root. It is set after the child process is created.
# The user and group can be specified either by their name or by their numeric
# IDs.
# Note: If the user is root, the executable needs to be started with
#       --allow-to-run-as-root option to work.
# Default Values: The user is set to master process running user by default.
#                 If the group is not set, the user's group is used.
#
RUN sed -i "s/user = www-data/user = $CONTAINER_USER/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = $CONTAINER_USER/g" /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p /var/www/html

RUN docker-php-ext-install pdo pdo_mysql

#
# Install PHP Extensions to proccess images
# See: https://hub.docker.com/_/php#:~:text=php%2Dsource%20delete-,PHP%20Core%20Extensions,-For%20example%2C%20if
# Uncomment bellow section to enable those extensions
#
#RUN apt update && apt install -y \
#		libfreetype6-dev \
#		libjpeg62-turbo-dev \
#		libpng-dev \
#        libwebp-dev \
#	&& docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
#	&& docker-php-ext-install -j$(nproc) gd \
#    && docker-php-ext-install exif

RUN apt update \
    && apt install -y libicu-dev \
    && docker-php-ext-install intl

#
# To run Artisan commands smoothly
# Install Composer
#
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN apt update && apt install -y \
    zip \
    unzip \
    libzip-dev
RUN docker-php-ext-install zip

# Install nodejs
RUN apt install -y ca-certificates curl gnupg \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
    && apt update \
    && apt install nodejs -y

#
# Install MySQL Client
# https://github.com/laravel/framework/issues/44542#issuecomment-1307026900
#
RUN apt update \
    && apt install -y default-mysql-client

USER ${CONTAINER_USER}

#
# -y, --fpm-config <file>        Specify alternative path to FastCGI process manager config file.
# -R, --allow-to-run-as-root     Allow pool to run as root (disabled by default)
#
CMD [ "php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R" ]

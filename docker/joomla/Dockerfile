ARG JOOMLA_VERSION

FROM joomla:${JOOMLA_VERSION:-latest}

ARG JOOMLA_FRAMEWORK_VERSION

RUN apt-get update && \
	# Install Node.js and Composer
	apt-get install -y curl && \
	curl -fsSL https://deb.nodesource.com/setup_24.x | bash - && \
	apt-get install -y nodejs&& \
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
	apt clean && rm -rf /var/lib/apt/lists/*

COPY . /attachments

WORKDIR /attachments

# Adjust Composer autoload paths for Joomla CMS source location
RUN sed -Ei 's/"Joomla\\\\CMS\\\\": "temp\/joomla\/libraries\/src\/"/"Joomla\\\\CMS\\\\": "\/var\/www\/html\/libraries\/src\/"/' composer.json && \
	composer dump-autoload

RUN sed -Ei 's/"joomla\/test": ".*"/"joomla\/test": "'"${JOOMLA_FRAMEWORK_VERSION}"'"/' composer.json && \
	sed -Ei 's/"joomla\/string": ".*"/"joomla\/string": "'${JOOMLA_FRAMEWORK_VERSION}'"/' composer.json && \
	sed -Ei 's/"joomla\/registry": ".*"/"joomla\/registry": "'${JOOMLA_FRAMEWORK_VERSION}'"/' composer.json && \
	sed -Ei 's/"joomla\/database": ".*"/"joomla\/database": "'${JOOMLA_FRAMEWORK_VERSION}'"/' composer.json && \
	sed -Ei 's/"joomla\/application": ".*"/"joomla\/application": "'${JOOMLA_FRAMEWORK_VERSION}'"/' composer.json && \
	sed -Ei 's/"joomla\/di": ".*"/"joomla\/di": "'${JOOMLA_FRAMEWORK_VERSION}'"/' composer.json && \
	sed -Ei 's/"joomla\/session": ".*"/"joomla\/session": "'${JOOMLA_FRAMEWORK_VERSION}'"/' composer.json

RUN composer update && \
	npm ci

WORKDIR /var/www/html
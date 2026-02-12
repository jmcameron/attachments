ARG CYPRESS_VERSION

FROM cypress/included:${CYPRESS_VERSION:-13.17.0}

RUN apt-get update && \
	apt-get install -y mariadb-client make zip && \
	rm -rf /var/lib/apt/lists/*
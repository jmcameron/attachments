FROM alpine:3.19

# Setup demo environment variables
ENV LANG=en_US.UTF-8 \
    LANGUAGE=en_US.UTF-8 \
    LC_ALL=en_US.UTF-8 \
    DISPLAY=:0.0

RUN echo "@testing https://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories
RUN apk update && \
    apk add \
    novnc@testing \
    bash \
    fluxbox \
    terminus-font \
    supervisor \
    tigervnc \
    websockify && \
    # py3-pip && \
    rm /var/cache/apk/*

# RUN pip3 install websockify

RUN ln -s /usr/share/novnc/vnc.html /usr/share/novnc/index.html

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

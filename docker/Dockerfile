FROM debian:wheezy

RUN apt-get update
RUN apt-get install php5-gd php5-mysql php5 apache2 libav-tools git -y

WORKDIR /

RUN mkdir www

WORKDIR /www

RUN git clone https://github.com/Sonerezh/sonerezh.git
RUN chown -R www-data: sonerezh/ && chmod -R 775 sonerezh/

ADD vhost.conf /etc/apache2/sites-enabled/000-default

RUN a2enmod php5
RUN a2enmod rewrite

WORKDIR /
RUN mkdir /data && chown -R www-data: data && chmod -R 775 data

EXPOSE 80

CMD /usr/sbin/apache2ctl -D FOREGROUND

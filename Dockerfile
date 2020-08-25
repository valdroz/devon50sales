FROM ubuntu:20.04

WORKDIR /

RUN apt-get update

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 mysql-server net-tools \ 
        php7.4 php7.4-curl php7.4-gd php7.4-json php7.4-mbstring php7.4-xml php7.4-zip \ 
        libapache2-mod-php7.4 php7.4-mysql -qq && \
        apt-get clean 
    
RUN mkdir -p /var/www/html/ocdevon

COPY ./oc/upload /var/www/html/ocdevon

RUN chown -R www-data:www-data /var/www

#RUN echo "bind-address = 0.0.0.0" >> /etc/mysql/my.cnf

ADD ./files/prep-mysql.sh .

RUN service mysql restart && sleep 10 && sh /prep-mysql.sh

ADD ./files/bootstrap.sh .

EXPOSE 80

#ENTRYPOINT sh bootstrap.sh && read 


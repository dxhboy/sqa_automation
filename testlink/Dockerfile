FROM  ubuntu

LABEL maintainer="xiaojinshui@casachina.com.cn"

RUN   sed -i s@/archive.ubuntu.com/@/mirrors.aliyun.com/@g /etc/apt/sources.list && \
      apt-get clean && apt-get update

RUN  DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 php7.0 libapache2-mod-php php-mysql php-gd vim git php-mbstring && \
     apt-get clean && sed -i s/\;extension=mbstring/extension=mbstring/g /etc/php/7.2/cli/php.ini && \
     git config --global http.sslVerify false && \
     git clone https://xiaojinshui%40casachina.com.cn:xjs10086@172.25.2.42/gz_lte_sqa/lte_testlink.git /var/www/html/testlink && \
     sed -i s/\'localhost\'/\'172.0.10.81\'/g `grep -rl localhost /var/www/html/testlink` && \
     chmod 777 -R /var/www/html/testlink

EXPOSE 80

CMD apachectl -D FOREGROUND

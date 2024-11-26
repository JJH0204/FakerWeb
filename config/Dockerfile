FROM php:7.4-apache

# 필요한 PHP 확장 모듈 설치
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 프로젝트 소스를 복사
COPY ./src /var/www/html

# Apache 설정 (선택적으로 설정 변경 가능)
RUN a2enmod rewrite

# 컨테이너에서 Apache 실행
CMD ["apache2-foreground"]

EXPOSE 80

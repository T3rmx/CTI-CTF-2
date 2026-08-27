FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    libapache2-mod-php \
    php \
    php-sqlite3 \
    php-cli \
    sqlite3 \
    openssh-server \
    openssh-client \
    curl \
    wget \
    net-tools \
    iputils-ping \
    iproute2 \
    dnsutils \
    procps \
    cron \
    sudo \
    vim \
    nano \
    tree \
    htop \
    netcat-openbsd \
    socat \
    tcpdump \
    nmap \
    git \
    python3 \
    python3-pip \
    ca-certificates \
    gcc \
    gdb \
    make \
    strace \
    ltrace \
    file \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Team tooling: pre-seed common privesc helper (skip silently if no network at build)
RUN mkdir -p /opt/t3rmx/tools \
    && (curl -fsSL https://github.com/peass-ng/PEASS-ng/releases/latest/download/linpeas.sh -o /opt/t3rmx/tools/linpeas.sh || true) \
    && (chmod +x /opt/t3rmx/tools/linpeas.sh || true)

RUN a2enmod rewrite headers

RUN sed -i 's/\/var\/www\/html/\/var\/www\/public/g' /etc/apache2/sites-available/000-default.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

RUN mkdir -p /run/sshd

COPY target/config/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

COPY target/config/php-settings.ini /etc/php/8.2/apache2/conf.d/99-t3rmx.ini

COPY target/config/sshd_config /etc/ssh/sshd_config

COPY target/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY target/startup/ /opt/t3rmx/startup/
COPY target/services/ /opt/t3rmx/services/
COPY target/scripts/ /opt/t3rmx/scripts/
RUN chmod -R +x /opt/t3rmx/startup/ /opt/t3rmx/services/ /opt/t3rmx/scripts/

COPY web/ /var/www/

COPY verifier/ /opt/t3rmx/verifier/

EXPOSE 80 22

HEALTHCHECK --interval=10s --timeout=5s --retries=5 --start-period=15s \
    CMD curl -f http://127.0.0.1:80/ || exit 1

ENTRYPOINT ["/entrypoint.sh"]

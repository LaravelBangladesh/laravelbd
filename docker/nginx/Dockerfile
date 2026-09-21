# syntax=docker/dockerfile:1.7

FROM nginx:1.29-alpine

# Baked in rather than bind-mounted: the production host holds no checkout of
# this repository, so there is no ./docker directory to mount from.
COPY docker/nginx/prod.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/cloudflare-real-ip.conf /etc/nginx/conf.d/cloudflare-real-ip.conf
COPY docker/nginx/origin-pull.conf /etc/nginx/conf.d/origin-pull.conf
COPY docker/nginx/cloudflare-origin-pull-ca.pem /etc/nginx/cloudflare-origin-pull-ca.pem

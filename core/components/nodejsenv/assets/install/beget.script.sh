#!/bin/bash
#Для выполнения скрипта необходимо дать общий доступ к папке .local
#(подробности тут https://beget.com/images/art/webapp/fm4.png)
#Также для вресий выше 11.15.0 требуется GLIBC_2.17 и GLIBCXX_3.4.18
version="v11.15.0";

cd ~/.local
wget https://nodejs.org/download/release/$version/node-$version-linux-x64.tar.xz
tar xJf node-$version-linux-x64.tar.xz --strip 1
rm node-$version-linux-x64.tar.xz
if cd bin
then
    if node -v
        then echo "Node.js установлен. Версия "$(node -v)
        else echo "Node.js не установлен."
    fi
    if npm -v
        then echo "NPM установлен. Версия "$(npm -v)
        else echo "NPM не установлен."
    fi
else echo "Node.js не установлен."
fi

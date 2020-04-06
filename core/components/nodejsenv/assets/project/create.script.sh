#!/bin/bash
#Проект может быть просто создан, склонирован с github, добавлен удалённо c github
#$1 - название проекта
#$2 - тип clone, local или remote
#$3 - путь к репозиторию
#$4 - почта git для коммита
#$5 - имя git для коммита
#$6 - конфигурация проекта

if [[ $# > 0 ]]
then
    DIR=$(dirname $(dirname $(dirname $(readlink -e $0))))
    ProjDIR=$DIR"/projects/"$1
    #Создать папку
    cd
    mkdir -p $ProjDIR
    if [[ $2 != '' ]]
    then
        #Если git то нужно создать репозиторий
        if [[ $2 == 'clone' ]] || [[ $2 == 'remote' ]]
        then
            if [[ $3 != '' ]]
            then
                if [[ $2 == 'clone' ]]
                then
                    echo "Клон репозитория"
                    git clone $3 $ProjDIR
                fi
                if [[ $2 == 'remote' ]]
                then
                    echo "Удалённый репозиторий"
                    cd $ProjDIR
                    git init
                    git remote add origin $3
                    git pull origin master
                    cd
                fi
            else echo "Укажите адрес репозитория"
            fi
        fi
        if [[ $2 == 'local' ]]
        then
            echo "Локальный репозиторий"
            cd $ProjDIR
            git init
            cd
        fi
        cd $ProjDIR
        git config --global user.email $4
        git config --global user.name $5
        cd
    fi
    
    projConfKey='default';
    if [[ $6 != '' ]]
    then
        projConfKey=$6
    fi
    projBase=$DIR"/assets/project/project."$projConfKey
    
    
    if [[ $2 == 'local' ]] || [[ $2 == '' ]]
    then
        cp -a $projBase/. $ProjDIR
    fi
    if [[ $2 == 'clone' ]] || [[ $2 == 'remote' ]]
    then
        cp $projBase/config.inc.php $ProjDIR/config.inc.php
        
        if [ -f $ProjDIR/gulpfile.js ]
        then
            cp $ProjDIR/gulpfile.js $ProjDIR/_gulpfile.js
        else
            cp $projBase/_gulpfile.js $ProjDIR/_gulpfile.js
        fi
        
        if [ -f $ProjDIR/.gitignore ]
        then
            for LINE in $(cat $projBase/..gitignore) ; do
                if [[ $(grep -F $LINE $ProjDIR/.gitignore) == '' ]]; then echo -en "\n$LINE" >> $ProjDIR/.gitignore; fi
            done
        else
            cp $projBase/..gitignore $ProjDIR/.gitignore
        fi
    fi
    
    cd $ProjDIR
    npm i
    
    #обновить локальный репозиторий
    if [[ $2 != '' ]]
    then
        cd $ProjDIR
        git add .
        git commit -m "Initial Commit"
        if [[ $2 == 'remote' ]]
        then
            git push origin master
        fi
        cd
    fi
else
    echo "Укажите имя проекта"
fi
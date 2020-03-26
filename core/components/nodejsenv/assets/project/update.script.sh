#!/bin/bash
#$1 - название проекта

if [[ $# > 0 ]]
then
    DIR=$(dirname $(dirname $(dirname $(readlink -e $0))))
    ProjDIR=$DIR"/projects/"$1
    cd
    
    #есть ли gulp и gulpfile.js?
    #идём в папку и вызываем node_modules/.bin/gulp
    if [[ $2 == '1' ]] && [ -f $ProjDIR'/gulpfile.js' ] && [ -e $ProjDIR'/node_modules/.bin/gulp' ]
    then
        cd $ProjDIR
        node_modules/.bin/gulp
        cd
    fi
    
    #проверяем есть ли git
    #делаем коммит
    if [[ $3 == '1' ]] && [[ $4 != '0' ]] && [ -d $ProjDIR'/.git' ]
    then
        cd $ProjDIR
        git add .
        git commit -m "$4"
        if [[ $5 == '1' ]]
        then
            git push origin master
        fi
        cd
    fi
    
else
    echo "Укажите имя проекта"
fi
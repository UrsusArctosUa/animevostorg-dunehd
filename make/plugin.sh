#!/bin/sh
MAKEPATH=$(dirname $(readlink -f "$0"))
CODEPATH=$MAKEPATH/../src
REPOPATH=$MAKEPATH/../

envirenment=dev
plugin_version=`git describe 2>/dev/null || echo 'v0.0.1'`

while getopts "e:v:i:" opt; do
  case $opt in
    e) envirenment="$OPTARG"
    ;;
    v) plugin_version="$OPTARG"
    ;;
    i) plugin_version_index="$OPTARG"
    ;;
  esac
done

echo "use envirenment $envirenment"
echo "use version marker $plugin_version"

if [ -f $MAKEPATH/${envirenment}.conf ]
then
    . $MAKEPATH/${envirenment}.conf
else
    (>&2 echo "fatal: file ${envirenment}.conf not found")
    exit 1
fi

if [ -z ${plugin_version_index+x} ] && command -v wget >/dev/null 2>&1; then
    old_info_content=`wget -O - ${plugin_update_url}update_info.xml 2>/dev/null`
    old_version_index=`echo $old_info_content | sed -n -e 's/^.*<version_index>\([0-9]*\)<\/version_index>.*$/\1/p'`
    plugin_version_index=`expr $old_version_index + 1`
fi
if [ -z ${plugin_version_index+x} ] && command -v curl >/dev/null 2>&1; then
    old_info_content=`curl ${plugin_update_url}update_info.xml 2>/dev/null`
    old_version_index=`echo $old_info_content | sed -n -e 's/^.*<version_index>\([0-9]*\)<\/version_index>.*$/\1/p'`
    plugin_version_index=`expr $old_version_index + 1`
fi
if [ -z ${plugin_version_index+x} ]; then
    echo >&2 "fatal: version index via -i must be specified or wget or curl installed"
    exit 1
fi

echo "use version index $plugin_version_index"

sed -e "s;%name%;$plugin_name;g" -e "s;%caption%;$plugin_caption;g" \
 -e "s;%version_index%;$plugin_version_index;g" -e "s;%version%;$plugin_version;g" \
 -e "s;%update_url%;$plugin_update_url;g" \
 $CODEPATH/tpl/dune_plugin.tpl > $CODEPATH/plugin/dune_plugin.xml

if [ ! -d $MAKEPATH/build/${envirenment}/plugin/ ]
then
    mkdir -p $MAKEPATH/build/${envirenment}/plugin/
fi
rm -rf $MAKEPATH/build/${envirenment}/plugin/*

CURDIR=$PWD
cd ${CODEPATH}/plugin/
file_zip=$MAKEPATH/build/${envirenment}/plugin/dune_plugin.zip
zip -qr $file_zip *
cd $CURDIR

file_tar=$MAKEPATH/build/${envirenment}/plugin/dune_plugin.tgz
tar -czf $file_tar -C ${CODEPATH}/plugin ` ls ${CODEPATH}/plugin `

plugin_tgz_md5=`md5sum -b $MAKEPATH/build/${envirenment}/plugin/dune_plugin.tgz | cut -d' ' -f1`
plugin_size=`du -sb ${CODEPATH}/plugin | cut -f1`

file_xml=$MAKEPATH/build/${envirenment}/plugin/update_info.xml
sed -e "s;%name%;$plugin_name;g" -e "s;%caption%;$plugin_caption;g" \
 -e "s;%version_index%;$plugin_version_index;g" -e "s;%version%;$plugin_version;g" \
 -e "s;%update_url%;$plugin_update_url;g" -e "s;%tgz_md5%;$plugin_tgz_md5;g" \
 -e "s;%size%;$plugin_size;g" -e "s;%critical%;$plugin_update_critical;g" \
 $CODEPATH/tpl/update_info.tpl > $file_xml
cp $file_zip $REPOPATH
deploy_files="$file_zip $file_tar $file_xml"
echo "Prepared files: $deploy_files"
deploy_script=`echo $plugin_deploy_command | sed -e "s;%deploy_files%;$deploy_files;g"`
eval $deploy_script
echo "Ok"
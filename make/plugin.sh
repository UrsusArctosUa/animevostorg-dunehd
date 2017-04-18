#!/bin/sh
MAKEPATH=$(dirname $(readlink -f "$0"))
CODEPATH=$MAKEPATH/../src

envirenment=dev
plugin_version=`git describe 2>/dev/null || echo 'v0.0.1'`

while getopts ":e:v:" opt; do
  case $opt in
    e) envirenment="$OPTARG"
    ;;
    v) plugin_version="$OPTARG"
    ;;
    \?) echo "Invalid option -$OPTARG" >&2
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

old_version_index=0
if [ -f $MAKEPATH/${envirenment}.lock ]
then
    old_version_index=`cat $MAKEPATH/${envirenment}.lock`
fi
plugin_version_index=` expr $old_version_index + 1 `
` echo $plugin_version_index > $MAKEPATH/${envirenment}.lock`

sed -e "s;%name%;$plugin_name;g" -e "s;%caption%;$plugin_caption;g" \
 -e "s;%version_index%;$plugin_version_index;g" -e "s;%version%;$plugin_version;g" \
 -e "s;%update_url%;$plugin_update_url;g" \
 $CODEPATH/tpl/dune_plugin.tpl > $CODEPATH/plugin/dune_plugin.xml

if [ ! -d $MAKEPATH/build/${envirenment}/plugin/ ]
then
    mkdir -p $MAKEPATH/build/${envirenment}/plugin/
fi

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

deploy_files="$file_zip $file_tar $file_xml"
echo "Prepared files: $deploy_files"
build_dir=$MAKEPATH/../build/
echo $plugin_deploy_command | sed -e "s;%deploy_files%;$deploy_files;g" -e "s;%build_dir%;$build_dir;g" | xargs xargs
echo "Ok"
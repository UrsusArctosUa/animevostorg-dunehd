#!/bin/sh
SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")

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

if [ -f $SCRIPTPATH/${envirenment}.conf ]
then
    . $SCRIPTPATH/${envirenment}.conf
else
    (>&2 echo "fatal: file ${envirenment}.conf not found")
    exit 1
fi

old_version_index=0
if [ -f $SCRIPTPATH/${envirenment}.lock ]
then
    old_version_index=`cat $SCRIPTPATH/${envirenment}.lock`
fi
plugin_version_index=` expr $old_version_index + 1 `
` echo $plugin_version_index > $SCRIPTPATH/${envirenment}.lock`

sed -e "s;%name%;$plugin_name;g" -e "s;%caption%;$plugin_caption;g" \
 -e "s;%type%;$plugin_type;g" -e "s;%version_index%;$plugin_version_index;g" \
 -e "s;%version%;$plugin_version;g"  -e "s;%update_url%;$plugin_update_url;g" \
 $SCRIPTPATH/${plugin_type}_dune_plugin.tpl > $SCRIPTPATH/../$plugin_type/dune_plugin.xml

if [ ! -d $SCRIPTPATH/build/${envirenment}/plugin/$plugin_type/ ]
then
    mkdir -p $SCRIPTPATH/build/${envirenment}/plugin/$plugin_type/
fi

cd $SCRIPTPATH/../$plugin_type/
file_zip=$SCRIPTPATH/build/${envirenment}/plugin/$plugin_type/dune_plugin.zip
zip -qr $file_zip *

file_tar=$SCRIPTPATH/build/${envirenment}/plugin/$plugin_type/dune_plugin.tgz
tar -czf $file_tar -C $SCRIPTPATH/../$plugin_type/ ` ls $SCRIPTPATH/../$plugin_type/ `

plugin_tgz_md5=`md5sum -b $SCRIPTPATH/build/${envirenment}/plugin/$plugin_type/dune_plugin.tgz | cut -d' ' -f1`
plugin_size=`du -sb $SCRIPTPATH/../$plugin_type/ | cut -f1`

file_xml=$SCRIPTPATH/build/${envirenment}/plugin/$plugin_type/update_info.xml
sed -e "s;%name%;$plugin_name;g" -e "s;%caption%;$plugin_caption;g" \
 -e "s;%version_index%;$plugin_version_index;g" -e "s;%version%;$plugin_version;g" \
 -e "s;%update_url%;$plugin_update_url;g" -e "s;%tgz_md5%;$plugin_tgz_md5;g" \
 -e "s;%size%;$plugin_size;g" -e "s;%critical%;$plugin_update_critical;g" \
 $SCRIPTPATH/${plugin_type}_update_info.tpl > $file_xml

deploy_files="$file_zip $file_tar $file_xml"
echo "Prepared files: $deploy_files"
build_dir=$SCRIPTPATH/../build/
echo $plugin_deploy_command | sed -e "s;%deploy_files%;$deploy_files;g" -e "s;%build_dir%;$build_dir;g" | xargs xargs
echo "Ok"
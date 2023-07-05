var pictureMixin = {
        data() {
            return {
                imageZoomModal : false,
                filePath:'',
                fileSize:'',
            }
        },
        methods: {
             onPickFile(ref){
                this.$refs[ref].click();
            },
            onFilePickedFromObj( event, obj, property){
                let files = event.target.files;
                let filename = files[0].name;
                if (filename.lastIndexOf('.') <=0 ) {
                    return alert('Please add valid image');
                }
                let fileReader = new FileReader();
                fileReader.addEventListener('load', ()=>{
                    // this.service.picPath=fileReader.result;
                    this[obj][property]=fileReader.result;
                })
                fileReader.readAsDataURL(files[0])
            },
            cancelSingleImage( event, obj, property){
                let files = event.target.files;
                this[obj][property] = ''
            },

            onFilePicked( event,  property){
                let files = event.target.files;
                let filename = files[0].name;
                this.fileSize = this.getFileSize(files[0].size);

                if (filename.lastIndexOf('.') <=0 ) {
                    return alert('Please add valid image');
                }
                let fileReader = new FileReader();
                fileReader.addEventListener('load', ()=>{
                    // this.service.picPath=fileReader.result;
                    this[property]=fileReader.result;
                })
                fileReader.readAsDataURL(files[0])
            },
            cancelImage( event,  property){
                let files = event.target.files;
                this[property] = ''
            },

            getFileSize(fileSize)
            {
                if ( (fileSize/1024) >= 1024 )
                {
                    fileSize= parseInt((fileSize/1024)/1024) + ' MB';
                }
                else{
                    fileSize=parseInt(fileSize/1024) + ' KB';
                }
                return fileSize;
            }

        },
    }



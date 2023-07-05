
    
    var tiptapMixin =   {
        components: {
            
        },
        data() {
            return {
               extensions: [
                                History,
                                Blockquote,
                                Underline,
                                Strike,
                                Italic,
                                ListItem,
                                BulletList,
                                OrderedList,
                                [Heading, {
                                    options: {
                                    levels: [1, 2, 3,4,5,6]
                                    }
                                }],
                                Bold,
                                Code,
                                HardBreak,
                                Link,
                                Image
                            ],
                            
            }
        },
        methods: {
            
        },
    }
  



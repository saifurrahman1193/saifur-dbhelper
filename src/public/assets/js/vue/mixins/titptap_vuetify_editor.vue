
<script>
    import { TiptapVuetify, Heading, Bold, Italic, Strike, Underline, Code, CodeBlock, Paragraph, BulletList, OrderedList, ListItem, Link, Blockquote, HardBreak, HorizontalRule, History,Image } from 'tiptap-vuetify'
    
    export default {
        components: {
            TiptapVuetify
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
  
</script>



var cpcore = {
    tools: {
        base64 : {

            encode : function (input){

                if( input === undefined || input === '' ){
                    return '';
                }

                try
                {
                    return window.btoa(unescape(encodeURIComponent( input )));
                }
                catch (ex){return input;}

            },

            decode : function (input){

                if( input === undefined || input === '' ){
                    return '';
                }

                try
                {
                    return decodeURIComponent(escape(window.atob( input )));
                }
                catch (ex){return input;}

            },

        },
    }
};
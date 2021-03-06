$(function () {
    $(".downloadButton").click(function(e)
	{
	    e.preventDefault();
	    
    	var extension = "." + $(this).data('ext');
    	var loadingMessage = $(this).data('loadingmessage');
    	if (loadingMessage == null || loadingMessage.length < 1)
    	{
    		loadingMessage = "Please be patient while we generate your document. This can take a minute or two.";
    	}
    	var cancelled = false;
    	var xhr;
    	
        
        $("<div id='loading' style='text-align: center'></div>").append("<p>" + loadingMessage + "</p>").dialog({
            height: 200,
            width: 400,
            modal: true,
            draggable: false,
            resizable: false,
            title: "Generating Document...",
            closeOnEscape: false,
        	close: function(event, ui) {
        		if (xhr != null)
    			{
        			cancelled = true;
        			xhr.abort();
    			}
        	}
        });

        xhr = $.ajax({
            url: this.href,
            success: function(data) {
            	if (data.indexOf(extension) < 0 || data.length > 100)
            	{
            		// Debug Info for extension: 
            		//.append("<p>IndexOf Returned: " + data.indexOf(extension) + " for the extension " + extension +"</p>")
            		$("<div id='errorMessage'></div>").append(data).dialog({
            			height: 300,
            			width: 900,
            			modal: true,
            			draggable: true,
            			resizeable: true,
            			title: "Error",
        				buttons: {
        					Ok: function() {
        						$( this ).dialog( "close" );
        					}
        				}
            		});
            	}
            	else
            	{
            	    window.location.href = data;
            	}
            },
            error: function(data) {
            	if (!cancelled)
        		{
		        	$("<div id='errorMessage'></div>").append(data).dialog({
		    			height: 500,
		    			width: 900,
		    			modal: true,
		    			draggable: true,
		    			resizeable: true,
		    			title: "Error"
		    			
		    		});
        		}
            },
            complete: function(data) {
            	$("#loading").remove();
            	xhr = null;
            	cancelled = false;
            }
        });
    });
});
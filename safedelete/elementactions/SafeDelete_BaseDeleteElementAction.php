<?php
namespace Craft;

abstract class SafeDelete_BaseDeleteElementAction extends BaseElementAction
{
    abstract public function getDeletionType();
    abstract public function getOriginalAction();
    abstract public function getDeletionHandle();

    public function getTriggerHtml()
    {
        $confirmMessage = $this->getConfirmationMessage();
        $deletionType = $this->getDeletionType();
        $originalAction = $this->getOriginalAction();
        $deletionHandle = $this->getDeletionHandle();

        $js = <<<EOT
(function()
{
	var trigger = new Craft.ElementActionTrigger({
		handle: '$deletionHandle',
		batch: false,
		validateSelection: function(\$selectedItems)
		{
			return true;
		},
		activate: function(\$selectedItems)
		{
		    if(confirm('$confirmMessage')) {
                 var ids = [];
                
                \$selectedItems.each(function(index, el) {
                    var \$el = \$(el);
                    ids.push(\$el.data('id'));
                });
                
                Craft.postActionRequest('safeDelete/tryDelete', {'ids': ids, type: '$deletionType'}, function(res) {
                    if(res.success && res.html) {
                       var \$html = $('<div class="modal">'+res.html+'</div>'),
                       modal = new Garnish.Modal(\$html);
                       
                       
                       \$html.find('.cancel').on('click', function() {
                        modal.hide();
                       });
                       
                       \$html.find('.submit').on('click', function() {
                           Craft.postActionRequest('safeDelete/forceDelete', {'ids': ids, type: '$deletionType'}, function(res) {
                                Craft.elementIndex.updateElements();
                                modal.hide();
                           });
                       });
                    } else if(res.success) {
                        Craft.elementIndex.updateElements();
                    }
                })
		    }
		}
	});
	
	//remove default delete
	 $('[data-action=$originalAction]').remove();
})();
EOT;

        craft()->templates->includeJs($js);
    }
}

import _ from 'lodash';

export default BacklogItemSelectedService;

BacklogItemSelectedService.$inject = [];

function BacklogItemSelectedService() {
    var self                   = this,
        selected_backlog_items = [];

    _.extend(self, {
        getCompactedSelectedBacklogItem    : getCompactedSelectedBacklogItem,
        getNumberOfSelectedBacklogItem     : getNumberOfSelectedBacklogItem,
        addSelectedItem                    : addSelectedItem,
        removeSelectedItem                 : removeSelectedItem,
        removeAllSelectedItems             : removeAllSelectedItems,
        getFirstSelectedItem               : getFirstSelectedItem,
        areThereMultipleSelectedBaklogItems: areThereMultipleSelectedBaklogItems,
        isDraggedBacklogItemSelected       : isDraggedBacklogItemSelected,
        multipleBacklogItemsAreDragged     : multipleBacklogItemsAreDragged,
        deselectAllBacklogItems            : deselectAllBacklogItems,
        reselectBacklogItems               : reselectBacklogItems
    });

    function getCompactedSelectedBacklogItem() {
        return _.compact(selected_backlog_items);
    }

    function getNumberOfSelectedBacklogItem() {
        return _.compact(selected_backlog_items).length;
    }

    function addSelectedItem(backlog_item, index) {
        selected_backlog_items[index] = backlog_item;
    }

    function removeSelectedItem(index) {
        selected_backlog_items[index] = undefined;
    }

    function removeAllSelectedItems() {
        selected_backlog_items.splice(0, selected_backlog_items.length);
    }

    function getFirstSelectedItem() {
        return _.first(getCompactedSelectedBacklogItem());
    }

    function areThereMultipleSelectedBaklogItems() {
        return selected_backlog_items.length > 1;
    }

    function isDraggedBacklogItemSelected(dragged_backlog_item_id) {
        var found = false;

        if (_.find(getCompactedSelectedBacklogItem(), { id: dragged_backlog_item_id })) {
            found = true;
        }

        return found;
    }

    function multipleBacklogItemsAreDragged(dragged_element) {
        var dragged_backlog_item_id = angular.element(dragged_element).data('item-id');

        _.forEach(getCompactedSelectedBacklogItem(), function(backlog_item) {
            backlog_item.hidden = true;

            if (backlog_item.id === dragged_backlog_item_id) {
                backlog_item.hidden   = false;
                backlog_item.selected = false;
                backlog_item.multiple = true;
            }
        });
    }

    function deselectAllBacklogItems() {
        _.forEach(getCompactedSelectedBacklogItem(), function(backlog_item) {
            backlog_item.hidden   = false;
            backlog_item.selected = false;
            backlog_item.multiple = false;
        });

        self.removeAllSelectedItems();
    }

    function reselectBacklogItems() {
        _.forEach(getCompactedSelectedBacklogItem(), function(backlog_item) {
            if (backlog_item.selected && backlog_item.hidden) {
                backlog_item.hidden = false;
            } else if (backlog_item.multiple) {
                backlog_item.selected = true;
            }
        });
    }
}

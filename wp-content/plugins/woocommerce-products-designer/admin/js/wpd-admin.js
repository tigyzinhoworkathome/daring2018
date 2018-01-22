(function ($) {
    'use strict';

    $(document).ready(function () {
        $(".edit-php.post-type-wpc-template .wrap h2 .add-new-h2").show();
        $(".js-example-basic-single").select2();

        $(document).on("click", ".wpc_img_upload", function (e) {
            e.preventDefault();
            var selector = $(this).attr('data-selector');
            var uploader = wp.media({
                title: 'Please set the picture',
                button: {
                    text: "Set Image"
                },
                multiple: false
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    $("#" + selector).attr('value', attachment.id);
                                    $("#" + selector + "_preview").html("<img src='" + attachment.url + "'>");
                                }
                        );
                    })
                    .open();
        });

        //we delay the init to avoid conflicts with plugin that hook on the select2 classes and create conflicts
        setTimeout(function () {
            $("#font").select2({allowClear: true});
        }, 500);

        load_select2();

        function load_select2(container)
        {
            if(typeof container=="undefined")
                container= "";
            $(container + " select.o-select2").each(function () {
//                console.log($(this));
                $(this).select2({allowClear: true});
            });
        }
        
        $(document).on('change', '#font', function () {
            console.log("yep");
            var name = $('#font  option:selected').text();
            var url = $('#font   option:selected').val();
            $('.font_auto_name').val(name);
            $('.font_auto_url').val(url);

        });

        //Cliparts add image
        $(document).on("click", "#wpc-add-clipart", function (e) {
            e.preventDefault();
            var selector = $(this).attr('data-selector');
            var trigger = $(this);
            var uploader = wp.media({
                title: 'Please set the picture',
                button: {
                    text: "Set Image"
                },
                multiple: true
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    var code = "<input type='hidden' value='" + attachment.id + "' name='selected-cliparts[]'>";
                                    code = code + "<span class='wpc-clipart-holder'><img src='" + attachment.url + "'>";
                                    code = code + "<label>Price: <input type='text' value='0' name='wpc-cliparts-prices[]'></label>";
                                    code = code + "<a href='#' class='button wpc-remove-clipart' data-id='" + attachment.id + "'>Remove</a></span>";
                                    $("#cliparts-container").prepend(code);
                                }
                        );
                    })
                    .open();
        });

        $(document).on("click", ".wpc-remove-clipart", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            $('#cliparts-form > input[value="' + id + '"]').remove();
            $(this).parent().remove();
        });

        $(document).on("click", ".o-add-font-file", function (e) {
            e.preventDefault();
            var uploader = wp.media({
                title: 'Please set the picture',
                button: {
                    text: "Select picture(s)"
                },
                multiple: false
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    var new_rule_index = $(".font_style_table tbody tr").length;
                                    var font_tpl = $("#wpd-font-tpl").val();
                                    var tpl = font_tpl.replace(/{index}/g, new_rule_index);
                                    $('.font_style_table tbody').prepend(tpl);
                                    $('#file_data_' + new_rule_index).find("input[type=hidden]").val(attachment.id);
                                    $('#file_data_' + new_rule_index).parent().find(".media-name").html(attachment.filename);
                                }
                        );
                    })
                    .open();
        });

        $(document).on("click", ".o-remove-font-file", function (e) {
            e.preventDefault();
            $(this).parent().find("input[type=hidden]").val("");
            $(this).parent().parent().find(".media-name").html("");
            $(this).parent().parent().remove();
        });

        $(document).on("click", "#wpc-customizer button", function (e) {
            e.preventDefault();
        });

        $(document).on("change", ".wpc-activate-part-cb", function (e) {
            var is_checked = $(this).is(":checked");
            var selector = $(this).attr('data-selector');
            var output_area = selector + "_preview";
            if (is_checked)
                $("#" + selector).attr('value', 0);
            else
                $("#" + selector).attr('value', '');
            $("#" + output_area).html("");

        });

        $(document).on("change", ".wpc-ovni-cb", function (e) {
            var is_checked = $(this).is(":checked");
            var selector = $(this).parent().find("input[type=hidden]");
            if (is_checked)
                selector.val(1);
            else
                selector.val(-1);

        });

        $(document).on("click", ".wpc_img_remove", function (e) {
            e.preventDefault();
            var is_active = $(this).siblings(".wpc-activate-part-cb").is(":checked");
            var selector = $(this).attr('data-selector');
            var output_area = selector + "_preview";
            if (is_active)
                $("#" + selector).attr('value', 0);
            else
                $("#" + selector).attr('value', "");

            $("#" + output_area).html("");
        });

//        $('[href="#wpc_output_setting_tab_data"]').on('click', function () {
//            products_tab_data("#wpc_output_setting_tab_data", "get_output_setting_tab_data_content");
//        });

//        $('[href="#wpc_parts_tab_data"]').on('click', function () {
//            products_tab_data("#wpc_parts_tab_data", "get_product_tab_data_content");
//        });

        $('[href="#wpc_related_products_tab_data"]').on('click', function () {
            var post_id = $("#post_ID").val();
            $.post(
                    ajax_object.ajax_url,
                    {
                        action: "get_related_products_content",
                        product_id: post_id,
//                        post_type: post_type,
//                        variations: variations_arr
                    },
                    function (data) {
                        $("#wpc_related_products_tab_data .related-products-container").html(data);
                    }
            );
        });

//        $('[href="#wpc_output_setting_tab_data"], [href="#wpc_parts_tab_data"], [href="#wpc_related_products_tab_data"]').trigger("click");

//        function products_tab_data(part_tab_id, action_name) {
//            var post_id = $("#post_ID").val();
//            var post_type = $("#product-type").val();
//            var variations_arr = new Object();
//            $.each($(".woocommerce_variation h3"), function () {
//                var elements = $(this).find("[name^='attribute_']");
//                var attributes_arr = [];
//                var variation_id = $(this).find('.remove_variation').first().attr("rel");
//                $.each(elements, function () {
//                    attributes_arr.push($(this).val());
//                });
//                variations_arr[variation_id] = attributes_arr;
//            });
//
//            $.post(
//                    ajax_object.ajax_url,
//                    {
//                        action: action_name,
//                        product_id: post_id,
//                        post_type: post_type,
//                        variations: variations_arr
//                    },
//            function (data) {
//                $(part_tab_id).html(data);
//            }
//            );
//        }

        $('a[href*="post-new.php?post_type=wpc-template"]').click(function (e)
        {
            e.preventDefault();
            $('#wpc-products-selector-modal').omodal("show");
        });
        
        $(".wpc_order_item .o-modal-trigger").click(function()
        {
            var target=$(this).data("target");
             $(target).omodal("show");
        });

        $("#wpc-select-template").click(function (e) {
            var selected_product = $('select[name=template_base_pdt]').val();
            if (typeof selected_product == 'undefined')
                alert("Please select a product first");
            else
            {
                var url = $('a[href*="post-new.php?post_type=wpc-template"]').first().attr("href");
                $(location).attr('href', url + "&base-product=" + selected_product);
            }
        });

        $("#wpc-settings .help_tip").each(function (i, e) {
            var tip = $(e).data("tip");
            $(e).tooltip({title: tip});
        });

        $("#wpc-settings [name='wpc-colors-options[wpc-color-palette]']").change(function () {
            var palette = $(this).val();
            if (palette == "custom")
                $("#wpd-predefined-colors-options").show();
            else
                $("#wpd-predefined-colors-options").hide();
        });

        $(document).on("keyup", "#wpc-settings [name='wpc-colors-options[wpc-custom-palette][]']", function (e) {
            var color = $(this).val();
            $(this).css("background-color", color);
        });

        $("#wpc-settings #wpc-add-color").click(function (e) {
            e.preventDefault();
            var new_color = '<div><input type="text" name="wpc-colors-options[wpc-custom-palette][]" class="wpc-color"><button class="button wpc-remove-color">Remove</button></div>';
            $("#wpc-settings .wpc-colors").append(new_color);
            load_colorpicker();
        });

        $(document).on("click", "#wpc-settings .wpc-remove-color", function (e) {
            e.preventDefault();
            $(this).parent().remove();
        });

        $(document).on("click", ".wpc-add-rule", function (e)
        {
            var new_rule_index = $(".wpc-rules-table tr").length;
            var group_index = $(this).data("group");
            var raw_tpl = $("#wpc-rule-tpl").val();
            var tpl1 = raw_tpl.replace(/{rule-group}/g, group_index);
            var tpl2 = tpl1.replace(/{rule-index}/g, new_rule_index);
            $(this).parents(".wpc-rules-table").find("tbody").append(tpl2);
            $(this).parents(".wpc-rules-table").find(".a_price").attr("rowspan", new_rule_index + 1);
        });

        $(document).on("click", ".wpc-add-group", function (e)
        {
            var new_rule_index = 0;
            var group_index = $(".wpc-rules-table").length;
            var raw_tpl = $("#wpc-first-rule-tpl").val();
            var tpl1 = raw_tpl.replace(/{rule-group}/g, group_index);
            var tpl2 = tpl1.replace(/{rule-index}/g, new_rule_index);
            var html = '<table class="wpc-rules-table widefat"><tbody>' + tpl2 + '</tbody></table>';
            $(".wpc-rules-table-container").append(html);
        });

        $(document).on("click", ".wpc-remove-rule", function (e)
        {
            var nb_rules = $(".wpc-rules-table tr").length;
            $(this).parents(".wpc-rules-table").find(".a_price").attr("rowspan", nb_rules - 1);
            $(this).parents("tr").remove();

        });

        $(document).on("keyup", ".color_field", function (e) {
            var color = $(this).val();
            $(this).css("background-color", color);
        });

        
        window.load_colorpicker = function ()
        {
            $('.wpc-color').each(function (index, element)
            {
                var e = $(this);
                var initial_color = e.val();
                e.css("background-color", initial_color);
                $(this).ColorPicker({
                    color: initial_color,
                    onShow: function (colpkr) {
                        $(colpkr).fadeIn(500);
                        return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                        e.css("background-color", "#" + hex);
                        e.val("#" + hex);
                    }
                });
            });
        }
load_colorpicker();
        function load_tabbed_panels(container)
        {
            $(container + " .TabbedPanels").each(function ()
            {
                var cookie_id = 'tabbedpanels_' + $(this).attr("id");
                var defaultTab = ($.cookie(cookie_id) ? parseInt($.cookie(cookie_id)) : 0);
                new Spry.Widget.TabbedPanels($(this).attr("id"), {defaultTab: defaultTab - 1});
            });
        }

        load_tabbed_panels("body");

//        $(".TabbedPanels").each(function ()
//        {
//            var cookie_id = 'tabbedpanels_' + $(this).attr("id");
//            var defaultTab = ($.cookie(cookie_id) ? parseInt($.cookie(cookie_id)) : 0);
//            new Spry.Widget.TabbedPanels($(this).attr("id"), {defaultTab: defaultTab - 1});
//        });

        $('.TabbedPanelsTab').click(function (event) {
            var cookie_id = 'tabbedpanels_' + $(this).parent().parent('.TabbedPanels').attr('id');
            $.cookie(cookie_id, parseInt($(this).attr('tabindex')));
        });

        $(document).on("click", ".wpd-add-media", function (e) {
            e.preventDefault();
            var trigger = $(this);
            var uploader = wp.media({
                title: 'Please select the background image',
                button: {
                    text: "Set"
                },
                multiple: false
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    trigger.parent().find("img").attr("src", attachment.url);
                                    trigger.parent().find("input[type=hidden]").val(attachment.id);
                                }
                        );
                    })
                    .open();
        });

        $(document).on("click", ".wpd-remove-media", function (e) {
            e.preventDefault();
            $(this).parent().find("img").attr("src", "");
            $(this).parent().find("input[type=hidden]").val("");
        });

        $(document).on("change", ".wpc-grid.wpc-grid-pad .wpc-color", function (e) {
            var color = $(this).val();
            $(this).css("background-color", color);
        });

        $(document).on("change", "#wpc-upload-options input[name='wpc-upload-options[visible-tab]']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-upload-options tr:not(:first-child) input[name^='wpc-upload-options'][type='checkbox']").prop("checked", checked);
        });

        $(document).on("change", "#wpc-texts-options input[name='wpc-texts-options[visible-tab]']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-texts-options tr:not(:first-child) input[name^='wpc-texts-options'][type='checkbox']").prop("checked", checked);
        });

        $(document).on("change", "#wpc-shapes-options input[name='wpc-shapes-options[visible-tab]']", function (e) {
            var checked = $(this).is(":checked");
            //console.log(checked);
            $("#wpc-shapes-options tr:not(:first-child) input[name^='wpc-shapes-options'][type='checkbox']").prop("checked", checked);
        });

        $(document).on("change", "#wpc-images-options input[name='wpc-images-options[visible-tab]']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-images-options tr:not(:first-child) input[name^='wpc-images-options'][type='checkbox']").prop("checked", checked);
        });

        $(document).on("change", "#wpc-designs-options input[name='wpc-designs-options[visible-tab]']", function (e) {
            var checked = $(this).is(":checked");
            $("#wpc-designs-options tr:not(:first-child) input[name^='wpc-designs-options'][type='checkbox']").prop("checked", checked);
        });

        if ($(".datatable").length)
        {
            $(".datatable").DataTable({"bAutoWidth": false});
        }

        $("#wpd-check-all-products").change(function ()
        {
            var is_checked = this.checked;
            $("#bulk-definition-table tbody input[type=checkbox]").prop('checked', is_checked);
        });

        $(document).on("woocommerce_variations_loaded", "#woocommerce-product-data", function (e) {
            load_tabbed_panels("#woocommerce-product-data");
            load_select2("#woocommerce-product-data");
        });
        
        $(document).on("click", ".wpd-add-cliparts", function (e) {
            e.preventDefault();
            var uploader = wp.media({
                title: 'Please set the picture',
                button: {
                    text: "Select picture(s)"
                },
                multiple: true
            })
                    .on('select', function () {
                        var selection = uploader.state().get('selection');
                        selection.map(
                                function (attachment) {
                                    attachment = attachment.toJSON();
                                    var url_without_root = attachment.url.replace(home_url, "");                                    
                                    setTimeout(function()
                                    {
                                        $('.add-rf-row').click();
                                        var trigger=$('#cliparts-container table .o-rf-row').last().find('.o-add-media');
                                        trigger.parent().find("input[type=hidden]").val(url_without_root);
                                        trigger.parent().find(".media-preview").html("<img src='" + attachment.url + "'>");
                                        trigger.parent().find(".media-name").html(attachment.filename);
                                        if (trigger.parent().hasClass("trigger-change"))
                                            trigger.parent().find("input[type=hidden]").trigger("propertychange");
                                    }, 200);
                                    
                                    
                                }
                        );
                    })
                    .open();
        });
        
        function get_output_fields_based_on_format()
        {
            $("[class*='show-if-']").hide();
            var selected_value=$('.config-output-format input:checked').val();
//            console.log(selected_value);
            if (selected_value.indexOf("pdf") >= 0)
            {
                $('.show-if-pdf').show();
                $('.hide-if-pdf').hide();
            }
            else
            {
                $('.show-if-pdf').hide();
                $('.hide-if-pdf').show();
            }
            
            if (selected_value.indexOf("jpg") >= 0)
                $('.show-if-jpg').show();
            else
                $('.show-if-jpg').hide();
            
            //Zip output
            $(".show-if-zip").hide();
            var zip=$('.zip-output input:checked').val();
            if (zip=='yes')
                $('.show-if-zip').show();
            else
                $('.show-if-zip').hide(); 
        }
        
         $(document).on("change", ".config-output-format input, .zip-output input", function (e)
        {
            get_output_fields_based_on_format();
                
        });
        
        if($('.config-output-format').length)
            get_output_fields_based_on_format();
        
        $(document).on("change", ".pdf-unit", function (e)
        {
            $(".hide-if-pixels").hide();
            var selected_value=$(this).val();
            if (selected_value.indexOf("px") >= 0)
                $('.hide-if-pixels').hide();
            else
                $('.hide-if-pixels').show();                
        });
        
        if($('.post-type-wpd-config .config-output-format').length)
            $('.post-type-wpd-config .config-output-format').trigger('change');
        if($('.zip-output').length)
            $('.zip-output').trigger('change');
        
        
        $('.run-wpd-upgrader').click('click', function () {
            var version=$(this).attr('data-version');
            var loader = $(this).siblings(".loading");
            if (confirm("It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run this updater now?")) {
                loader.show();
                $.post(
                        ajax_object.ajax_url,
                        {
                            action: 'run_updater',
                            version: version
                        },
                function (data) {
                    loader.hide();
                    alert('Done.');
                }
                );
            }

        });
    });

// //    (function ($) {
//     $.each(['show', 'hide'], function (i, ev) {
//       var el = $.fn[ev];
//       $.fn[ev] = function () {
// //          console.log(this);
//         if(this.hasClass("wpc-sh-triggerable"))
//         {
//             this.trigger(ev);            
//         }
//         return el.apply(this, arguments);
//       };
//     });
//});

})(jQuery);

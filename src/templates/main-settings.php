<?php

?>
    <div id="wp-post-visits-wizard-main" class="container">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-primary p-m">
                    <div class="container-fluid text-center">
                        <h1 class="display-4">Post Visits Wizard Config</h1>
                        <p class="lead">Just select the desired types, categories and tags you want to manage</p>
                        <p class="lead">After that all of them will register their visits count and get re ordered as
                            well in listings</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-4">
                <h4 class="my-3 text-center">Post types</h4>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action " v-for="type in types"
                       :class="{'active': type.active}">
                        {{type.name}}
                        <span class="badge badge-pill badge-dark float-right" v-if="type.active"> ON </span>
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <h4 class="my-3 text-center">Post categories</h4>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action " v-for="category in categories"
                       :class="{'active': category.active}">
                        {{category.name}}
                        <span class="badge badge-pill badge-dark float-right" v-if="category.active"> ON </span>
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <h4 class="my-3 text-center">Post tags</h4>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action " v-for="tag in tags"
                       :class="{'active': tag.active}">
                        {{tag.name}}
                        <span class="badge badge-pill badge-dark float-right" v-if="tag.active"> ON </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php do_action( 'wp-post-visits-wizard-app' ); ?>
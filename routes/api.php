<?php
setlocale(LC_ALL, 'tr_TR.utf8');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get("/", function (Request $request) {
    dd($request->getSchemeAndHttpHost());
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("getNutritionList", "api\\panel\\nutrients\\indexController@index");

Route::group(['namespace' => 'api'], function () {
    Route::group(["namespace" => "theme", "as" => "theme."], function () {
        Route::group(['namespace' => 'home', "as" => "home.", "prefix" => "home"], function () {
            Route::get("/", "indexController@index")->name("index");
            Route::get("/dieticians", "indexController@dieticians")->name("dieticians");
            Route::get("/recipes", "indexController@recipes")->name("recipes");
            Route::get("/exercisecategories", "indexController@exercisecategories")->name("exercisecategories");
            Route::get("/categoryexercises/{slug}", "indexController@categoryexercises")->name("categoryexercises");
            Route::post("/searchdieticians", "indexController@searchdieticians")->name("dieticianssearch");
            Route::post("/searchrecipes", "indexController@searchrecipes")->name("recipessearch");
            Route::post("/searchexercisecategories", "indexController@searchexercisecategories")->name("exercisecategoriessearch");
            Route::post("/searchcategoryexercises/{slug}", "indexController@searchcategoryexercises")->name("categoryexercisessearch");
            Route::get("/search", "indexController@search")->name("search");
        });

        Route::group(['namespace' => 'recipeCategories', "as" => "recipeCategories.", "prefix" => "recipe-categories"], function () {
            Route::get("/", "indexController@index")->name("index");
            Route::get("/{slug}", "indexController@detail")->name("detail");
        });
        Route::group(['namespace' => 'blog', "as" => "blog.", "prefix" => "blog"], function () {
            Route::get("/", "indexController@index")->name("index");
            Route::get("/{slug}", "indexController@detail")->name("detail");
            Route::get("/category/{id}", "indexController@getPostsByCategory")->name("getPostsByCategory");
        });
        Route::group(['namespace' => 'recipes', "as" => "recipes.", "prefix" => "recipes"], function () {
            Route::get("/{slug}", "indexController@index")->name("index");
            Route::get("/details/{slug}", "indexController@show")->name("show");
        });
        Route::group(['namespace' => 'nutrients', "as" => "nutrients.", "prefix" => "nutrients"], function () {
            Route::get("/", "indexController@index")->name("index");
            Route::get("/{slug}", "indexController@detail")->name("detail");
        });
        Route::group(['namespace' => 'appointments', "as" => "appointments.", "prefix" => "appointments"], function () {
            Route::get("/", "indexController@index")->name("index");
            Route::get("/{slug}", "indexController@show")->name("show");
            Route::post("/", "indexController@store")->name("store");
        });
        Route::group(['namespace' => 'informations', "as" => "informations.", "prefix" => "informations"], function () {
            Route::get("/cities", "indexController@city")->name("city");
            Route::get("/country", "indexController@country")->name("country");
            Route::get("/towns", "indexController@town")->name("town");
            Route::get("/districts", "indexController@district")->name("district");
            Route::get("/neighborhoods", "indexController@neighborhood")->name("neighborhood");
        });
        Route::group(['namespace' => 'criteria', "as" => "criteria.", "prefix" => "criteria"], function () {
            Route::get("/", "indexController@index")->name("index");
            Route::get("/{slug}", "indexController@detail")->name("detail");
        });
        Route::group(['namespace' => 'exercises', "as" => "exercises.", "prefix" => "exercises"], function () {
            Route::get("/", "indexController@index")->name("index");
            Route::get("/{slug}", "indexController@detail")->name("detail");
        });
        Route::group(["namespace" => "users", "as" => "users.", "prefix" => "users"], function () {
            Route::get("/login", "indexController@login")->name("login");
            Route::post("/login", "indexController@login")->name("login");
            Route::post("/register", "indexController@register")->name("register");
            Route::middleware('api-token')->group(function () {
                Route::get("/", "indexController@index")->name("index");
                Route::get("/profile", "indexController@profile")->name("profile");
                Route::post("/update", "indexController@update")->name("update");
                Route::post("/pass_update", "indexController@pass_update")->name("pass_update");
                Route::post("/dietician-update", "indexController@dieticianUpdate")->name("dietician_update");
                Route::post("/update-allergens", "indexController@storeAllergen")->name("update-allergen");
                Route::post("/logout", "indexController@logout")->name("logout");
                Route::post("/update-liked-foods", "indexController@storeLike")->name("update-like");
                Route::post("/update-unliked-foods", "indexController@storeUnlike")->name("update-unlike");
                Route::get("/diseases", "indexController@getDiseases")->name("get-diseases");
                Route::get("/meals", "indexController@getMeals")->name("get-meals");
            });
        });
        Route::group(["namespace" => "dieticians", "as" => "dieticians.", "prefix" => "dieticians"], function () {
            Route::get("/dieticians", "indexController@dieticians")->name("dieticians");
            Route::get("/dieticians/{slug}", "indexController@show")->name("showdieticians");
            Route::get("/login", "indexController@login")->name("login");
            Route::post("/login", "indexController@login")->name("login");
            Route::post("/register", "indexController@register")->name("register");
            Route::middleware('api-token')->group(function () {
                Route::get("/", "indexController@index")->name("index");
                Route::get("/profile", "indexController@profile")->name("profile");
                Route::post("/update", "indexController@update")->name("update");
                Route::post("/pass_update", "indexController@pass_update")->name("pass_update");
                Route::post("/logout", "indexController@logout")->name("logout");
            });
        });
    });

    Route::group(['namespace' => 'panel', "as" => "panel.", "prefix" => "panel"], function () {
        Route::group(['namespace' => 'login', "as" => "login.", "prefix" => "login"], function () {
            Route::post("/", "indexController@login")->name("login");
        });
        Route::middleware('api-token')->group(function () {
            Route::middleware('admin-status')->group(function () {
                Route::group(["namespace" => "datatables", "as" => "datatables.", "prefix" => "datatables"], function () {
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                    Route::get('/is-active-setter', 'indexController@isActiveSetter');
                    Route::get('/is-cover-setter', 'indexController@isCoverSetter');
                    Route::delete('/delete-file', 'indexController@deleteFile');
                });
                Route::group(['namespace' => 'settings', "as" => "settings.", "prefix" => "settings"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(['namespace' => 'users', "as" => "users.", "prefix" => "users"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(['namespace' => 'dieticians', "as" => "dieticians.", "prefix" => "dieticians"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(['namespace' => 'sliders', "as" => "sliders.", "prefix" => "sliders"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::match(['get', 'post'],"/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(['namespace' => 'foods', "as" => "foods.", "prefix" => "foods"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(['namespace' => 'inovices', "as" => "inovices.", "prefix" => "inovices"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "nutrients", "as" => "nutrients.", "prefix" => "nutrients"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "edietfoods", "as" => "edietfoods.", "prefix" => "e-diet-foods"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "diseases", "as" => "diseases.", "prefix" => "diseases"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "criteria", "as" => "criteria.", "prefix" => "criteria"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "recipes", "as" => "recipes.", "prefix" => "recipes"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "Blog", "as" => "blog.", "prefix" => "blog"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });

                Route::group(["namespace" => "blogCategory", "as" => "blogCategory.", "prefix" => "blog-categories"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "recipeCategories", "as" => "recipe-categories.", "prefix" => "recipe-categories"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "exerciseCategories", "as" => "exercise-categories.", "prefix" => "exercise-categories"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "exercises", "as" => "exercises.", "prefix" => "exercises"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "datatable", "as" => "datatable.", "prefix" => "datatable"], function () {
                    Route::get("/", "indexController@index")->name("index");
                });
            });
        });
    });

    Route::group(['namespace' => 'dietician', "as" => "dietician.", "prefix" => "dietician"], function () {
        Route::group(['namespace' => 'login', "as" => "login.", "prefix" => "login"], function () {
            Route::post("/", "indexController@login")->name("login");
        });
        Route::group(['namespace' => 'register', "as" => "register.", "prefix" => "register"], function () {
            Route::post("/", "indexController@register")->name("register");
        });
        Route::middleware('api-token')->group(function () {
            Route::middleware('dietician-status')->group(function () {
                Route::group(['namespace' => 'profile', "as" => "profile.", "prefix" => "profile"], function () {
                    Route::get("/", "indexController@profile")->name("profile");
                    Route::post("/update", "indexController@update")->name("update");
                    Route::post("/pass_update", "indexController@pass_update")->name("pass_update");
                    Route::post("/logout", "indexController@logout")->name("logout");
                    Route::post("/appointment-conf", "indexController@appointment_conf")->name("appointment_conf");
                });
                Route::group(["namespace" => "datatables", "as" => "datatables.", "prefix" => "datatables"], function () {
                    Route::get('/get-all', 'indexController@getAll')->name("index");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                    Route::get('/is-active-setter', 'indexController@isActiveSetter');
                    Route::get('/is-cover-setter', 'indexController@isCoverSetter');
                    Route::delete('/delete-file', 'indexController@deleteFile');
                });
                Route::group(['namespace' => 'settings', "as" => "settings.", "prefix" => "settings"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                });
                Route::group(["namespace" => "Blog", "as" => "blog.", "prefix" => "blog"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(['namespace' => 'users', "as" => "users.", "prefix" => "users"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                });
                Route::group(['namespace' => 'dieticians', "as" => "dieticians.", "prefix" => "dieticians"], function () {
                    Route::get("/", "indexController@index")->name("index");

                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                });
                Route::group(['namespace' => 'users', "as" => "users.", "prefix" => "users"], function () {
                    Route::get("/", "indexController@index")->name("index");

                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    /*
                     * Buraya Post at get sorgusu her zaman çalışmaz o yüzden match kullandım (get-user)
                     */
                    Route::match(['get','post'],"/get-user", "indexController@getUser")->name("getUser");
                    /*
                     * birthcalc attığın değer 1991-01-30 şeklinde olucak eğer kullanıcıda yaş yoksa 1-2-3 month gibi dönüş verir
                     */
                    Route::match(['get','post'],"/birthcalc", "indexController@userBirthCalc")->name("userBirthCalc");
                    /*
                     * Danışan Hastalık kayıt /user-diseases
					 * Danışan Alerjik Yemekler
					 * Danışan Sevilmeyen Besinler
                     */
                    Route::match(['get','post'],"/user-diseases", "indexController@userDiseases")->name("userDiseases");
                    Route::match(['get','post'],"/user-allergenfoods", "indexController@userAllergenFoods")->name("userAllergenFoods");
                    Route::match(['get','post'],"/user-unlovedfoods", "indexController@userUnlovedFoods")->name("userUnlovedFoods");
                    Route::get("/user-diseases-get", "indexController@getAllDiseases")->name("userDiseasesGet");


                    /*
                     * Diyet Öğün Kaydı
                     */
                    Route::post("/user-meal", "indexController@dietMeal")->name("dietMeal");
                    Route::post("/user-mail", "indexController@userMail")->name("userMail");
                });
                Route::group(["namespace" => "recipes", "as" => "recipes.", "prefix" => "recipes"], function () {
                    Route::get("/", "indexController@index")->name("index");
                    Route::post("/create", "indexController@store")->name("store");
                    Route::get("/create", "indexController@save")->name("save");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
                });
                Route::group(["namespace" => "ediets", "as" => "ediets.", "prefix" => "e-diets"], function () {
                    Route::get("/", "indexController@index")->name("index");
					Route::get("user-dietplans-get", "indexController@dietPlans")->name("dietPlans");
                    Route::get("/create/{id}", "indexController@save")->name("save");
                    Route::post("/create/{id}", "indexController@save")->name("save");
                    //Route::post("/create", "indexController@store")->name("store");
                    Route::post("/create-file/{id}", "indexController@fileStore")->name("fileStore");
                    Route::get("/update/{id}", "indexController@edit")->name("edit");
                    Route::post("/update/{id}", "indexController@update")->name("update");
                    Route::delete("/delete/{id}", "indexController@destroy")->name("destroy");
                    Route::get('/get-all', 'indexController@getAll')->name("getAll");
                    Route::get('/get-by-search', 'indexController@getBySearch');
                    Route::get('/get-by-order', 'indexController@getByOrder');
					Route::post("/user-stresscalc","indexController@stresfaccalc");
                });
            });
        });
    });
});

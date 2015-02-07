A utility to get path info:  
for example, a request uri could be:  
/product/list.php/category/1/sort/price/asc  
in list.php you want to get the information includes:  
catetory: id=1  
sort: "price", "asc"

PathInfoHelper is help to make this easier.

#Usage:  
*before using this utility class, need to create an instance first:*  
*$helper = new PathInfoHelper();*

##Get request URI
This is a shortcut for $_SERVER["REQUEST_URI"];

    $helper->RequestURI

##Get current script file
Get the requested php script filename even if it is hidden by rewrite rule.

    $helper->ScriptFile

##Get all path parameter string

    $helper->ParameterString

for the example above, the result will be "/category/1/sort/price/asc"

##Get all path parameter array
same as `ParameterString`, but break them into an array

    $helper->Parameters

##Get single parameter value
if an parameter has only one value followed, get this single value.

    $helper->getParameterValue($paramName)

for the example above, "/category/1"  
`$helper->getParameterValue("category")` will returns "1"

##Get multi parameter value
if an parameter has more than one value, you can use `getParameterValues` method

    $helper->getParameterValues($paramName, $valueCount)

for the example above, "/sort/price/asc"  
`$helper->getParameterValues("sort", 2)` will returns array("price", "asc")
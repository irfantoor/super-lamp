# what is super-lamp?

Super lamp illuminates the dependency chain of your application. It can also be
used to keep track of the dependencies of your code.

# who needs it?

You can use it in following cases:
- creating a dependency tree of your application
 - to check/locate the problem of inclusion of a library in its entirety by a 
 dependency manager
 - to check the conflicts between third party dependencies
 - to do an audit of the chain of dependencies against known vulnerabilities

# where can super-lamp be used?

Super lamp help you tack your dependency structure of your php packages or libraries
hosted at:
 - https://packagist.org

# how to use super-lamp?

```sh
# install it using composer globally
$ composer global require irfantoor/super-lamp

# suppose you have your own bin files in the ~/bin folder
$ cd ~/bin
$ ln -s ~/.composer/vendor/irfantoor/super-lamp/bin/super-lamp .

# verify if it runs by:
$ ./super-lamp -h

# now you can go to your package folder
$ cd ~/github/irfantoor/super-lamp
$ super-lamp

# and voila, the result:
#
# note: the result is in different colors, so as to distinguish between
# the required and actual versions and the require and the --dev packages
# in the d-chain

irfantoor/super-lamp

bring your dependency chain to light

keywords    : super, lamp, light, dependency, chain
type        : library
authors     : Irfan TOOR<email@irfantoor.com>

require     : irfantoor/command       ~0.5     [0.5.2]       
              irfantoor/debug         ~0.6     [0.6.1]       

require-dev : irfantoor/test          ~0.7     [0.7.5]       

d-chain     : 
              irfantoor/command       ~0.5     [0.5.2]       
              - irfantoor/debug       ~0.6    
              - irfantoor/terminal    ~0.1    
              - php                   >= 7.3  
              - irfantoor/mini-loader ^0.1.1  
              irfantoor/debug         ~0.6     [0.6.1]       
              - irfantoor/terminal    ~0.1    
              - php                   >= 7.3  
              - irfantoor/mini-loader ~0.1    
              irfantoor/test          ~0.7     [0.7.5]       
              - irfantoor/command     ~0.5    
              - php                   >= 7.3  
              irfantoor/terminal               [0.1.3]       
              - php                   >= 7.3  
              - irfantoor/mini-loader ~0.1  
```

# why super-lamp?

After having problems including my code irfantoor/engine using composer and
not finding any option in the dependency manager to list the dependencies in
a heirarchical way, I created super-lamp (the name was suggested by github ...) to show me the dependency chain of the project

NOTE: It only shows the dependency of the packages included. In the next versions
mechanism to display the --dev dependencies might as well be included.
